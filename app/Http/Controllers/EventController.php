<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use function Psy\debug;

class EventController extends Controller
{
  public function __construct()
  {
    parent::__construct();
  }

  public function Register(Request $request)
  {
    //Input = info event
    //output = event terdaftar di realtime db /events/uniqueid
    $name = $request->input('nama');
    $date = $request->input('date');
    $price = $request->input('price');
    $place = $request->input('place');
    $time = $request->input('time');
    $ticket = $request->input('ticket');
    $description = $request->input('description');
    $organizer = $request->input('organizer');
    $status = $request->input('status');

    $category = explode(',', $request->input('category'));

    try {
      $data = [
        'name' => $name,
        'date' => $date,
        'price' => $price,
        'desc' => $description,
        'organizer' => $organizer,
        'status' => $status,
        'category' => $category,
        'place' => $place,
        'time' => $time,
        'ticket' => $ticket,
        'ticketleft' => $ticket
      ];
      $id = $this->database->getReference('/events')->push()->getKey();
      $this->database->getReference('/events/' . $id)->set($data);

      return response()->json(['message' => 'success'], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'failed'], 500);
    }
  }

  public function EventData($id)
  {
    //Input = id event
    //output = semua info event dalam bentuk array
    //cara manggil category bisa {{ implode(' ', $event['category']) }}, manggil yang lain $event['info']
    try {
      $event = $this->database->getReference('/events/' . $id)->getValue();

      if (!$event) {
        return response()->json(['message' => "event not found"], 404);
      }

      return response()->json(['message' => 'success', 'event' => $event], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'failed'], 500);
    }
  }

  public function AllEventData()
  {
    //Input = id event
    //output = semua info event dalam bentuk array
    //cara manggil category bisa {{ implode(' ', $event['category']) }}, manggil yang lain $event['info']
    try {
      $events = $this->database->getReference('/events')->getValue();

      return response()->json(['message' => 'success', 'events' => $events], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'failed'], 500);
    }
  }

  public function AddRegisteredUserEvent(Request $request)
  {
    //Input = id event, uid user
    //output = id event masuk di /users/uid/registeredevent/id
    $uid = $request->input('uid');
    $id = $request->input('id');
    $ticketBuy = $request->input('ticket');
    $timelimit = $request->input('timelimit');

    try {
      $event = $this->database->getReference('/events/' . $id)->getValue();
      if (!$event) {
        return response()->json(['message' => 'event not found'], 404);
      }

      $user = $this->database->getReference('/users/' . $uid)->getValue();
      if (!$user) {
        return response()->json(['message' => 'user not found'], 404);
      }

      $eventNotRegistered = $this->database->getReference('/users/' . $uid . '/registeredevents/' . $id)->getValue();
      if ($eventNotRegistered === null) {
        $eventStatus = $this->database->getReference('/events/' . $id . '/status')->getValue();
        if ($eventStatus === 'open') {

          $ticketleft = $this->database->getReference('/events/' . $id . '/ticketleft')->getValue();
          if ($ticketleft - $ticketBuy < 0) {
            return response()->json(['message' => 'Insufficient tickets available'], 200);
          }
          $ticketleft = $ticketleft - $ticketBuy;
          $this->database->getReference('/events/' . $id . '/ticketleft')->set($ticketleft);
          if ($ticketleft === 0) {
            $this->database->getReference('/events/' . $id . '/status')->set("closed");
          }
          $harga = $this->database->getReference('/events/' . $id . '/price')->getValue();
          $harga = $harga * $ticketBuy;

          $this->database->getReference('/users/' . $uid . '/registeredevents/' . $id . "/status")->set("Lunas");
          $this->database->getReference('/users/' . $uid . '/registeredevents/' . $id . "/ticket")->set($ticketBuy);
          $this->database->getReference('/users/' . $uid . '/registeredevents/' . $id . "/total")->set($harga);
          $this->database->getReference('/users/' . $uid . '/registeredevents/' . $id . "/timelimit")->set($timelimit);
        }
      } else {
        return response()->json(['message' => 'already bought the ticket'], 200);
      }

      return response()->json(['message' => 'success'], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'failed'], 500);
    }
  }

  public function Lunas($id, $uid)
  {
    try {
      $this->database->getReference('/users/' . $uid . '/registeredevents/' . $id . "/status")->set("Lunas");
      return response()->json(['message' => 'success'], 200);
    } catch (\Exception $e) {
      return response()->json(['success' => 'failed'], 500);
    }
  }

  public function FeaturedEvent()
  {
    try {
      $events = $this->database->getReference('/events')
        ->orderByChild('ticketleft')
        ->getSnapshot()
        ->getValue();

      $featuredEvents = [];

      foreach ($events as $eventId => $event) {
        $totalBuy = (($event['ticket'] - $event['ticketleft']) / $event['ticket']) * 100;
        $featuredEvents[$eventId] = $event;
        $featuredEvents[$eventId]['totalBuy'] = $totalBuy;
      }

      $totalBuy = array_column($featuredEvents, 'totalBuy');

      array_multisort($featuredEvents, SORT_ASC, $totalBuy);

      return response()->json(['message' => 'success', 'events' => array_splice($featuredEvents, 0, 3)], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'failed'], 400);
    }
  }

  public function OngoingEvent()
  {
    try {
      $events = $this->database->getReference('/events')
        ->orderByChild('date')
        ->getSnapshot()
        ->getValue();

      $currentYear = date('Y');
      $currentMonth = date('m');

      $ongoingEvents = [];

      foreach ($events as $eventId => $event) {
        $eventDate = explode('-', $event['date']);
        Log::debug($currentYear . $eventDate[0]);
        if ($currentYear === $eventDate[0] && $currentMonth === $eventDate[1]) {
          $ongoingEvents[$eventId] = $event;
        }
        if (count($ongoingEvents) === 3) {
          break;
        }
      }

      return response()->json(['message' => 'success', 'events' => $ongoingEvents], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'failed'], 400);
    }
  }
}
