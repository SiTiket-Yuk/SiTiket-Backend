<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventController extends Controller
{
  public function __construct()
  {
    parent::__construct();
  }

  public function register(Request $request)
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

      return response()->json(['success' => true], 200);
    } catch (\Exception $e) {
      return response()->json(['success' => false], 400);
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
        return response()->json(['success' => false], 404);
      }

      return response()->json(['success' => true, 'event' => $event], 200);
    } catch (\Exception $e) {
      return response()->json(['success' => false], 500);
    }
  }

  public function AllEventData()
  {
    //Input = id event
    //output = semua info event dalam bentuk array
    //cara manggil category bisa {{ implode(' ', $event['category']) }}, manggil yang lain $event['info']
    try {
      $events = $this->database->getReference('/events')->getValue();

      return response()->json(['success' => true, 'events' => $events], 200);
    } catch (\Exception $e) {
      return response()->json(['success' => false], 500);
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
        return response()->json(['success' => false], 404);
      }

      $user = $this->database->getReference('/users/' . $uid)->getValue();
      if (!$user) {
        return response()->json(['success' => false], 404);
      }

      $eventNotRegistered = $this->database->getReference('/users/' . $uid . '/registeredevents/' . $id)->getValue();
      if ($eventNotRegistered === null) {
        $eventStatus = $this->database->getReference('/events/' . $id . '/status')->getValue();
        if ($eventStatus === 'open') {

          $ticketleft = $this->database->getReference('/events/' . $id . '/ticketleft')->getValue();
          if ($ticketleft - $ticketBuy < 0) {
            return response()->json(['success' => false], 200);
          }
          $ticketleft = $ticketleft - $ticketBuy;
          $this->database->getReference('/events/' . $id . '/ticketleft')->set($ticketleft);
          if ($ticketleft === 0) {
            $this->database->getReference('/events/' . $id . '/status')->set("closed");
          }
          $harga = $this->database->getReference('/events/' . $id . '/price')->getValue();
          $harga = $harga * $ticketBuy;

          $this->database->getReference('/users/' . $uid . '/registeredevents/' . $id . "/status")->set("Belum Lunas");
          $this->database->getReference('/users/' . $uid . '/registeredevents/' . $id . "/ticket")->set($ticketBuy);
          $this->database->getReference('/users/' . $uid . '/registeredevents/' . $id . "/total")->set($harga);
          $this->database->getReference('/users/' . $uid . '/registeredevents/' . $id . "/timelimit")->set($timelimit);
        }
      } else {
        return response()->json(['success' => false, 'message' => 'Lu udah daftar eventnya'], 400);
      }



      return response()->json(['success' => true], 200);
    } catch (\Exception $e) {
      return response()->json(['success' => false], 500);
    }
  }

  public function Lunas($id, $uid)
  {
    try {
      $this->database->getReference('/users/' . $uid . '/registeredevents/' . $id . "/status")->set("Lunas");
      return response()->json(['success' => true], 200);
    } catch (\Exception $e) {
      return response()->json(['success' => false], 500);
    }
  }

  public function FeaturedEvent()
  {
    try {
      $events = $this->database->getReference('/events')->getValue();
      $featuredEvents = [];

      $totalBuyArray = [];

      foreach ($events as $id => $event) {
        $ticketStatus = $this->database->getReference('/events/' . $id . '/status')->getValue();

        if ($ticketStatus === 'closed') {
          continue;
        }
        $eventDate = $this->database->getReference('/events/' . $id . '/date')->getValue();

        if (strtotime($eventDate) <= strtotime(date('Y-m-d'))) {
          continue;
        }

        $ticket = $this->database->getReference('/events/' . $id . '/ticket')->getValue();
        $ticketleft = $this->database->getReference('/events/' . $id . '/ticketleft')->getValue();

        $totalBuy = (($ticket - $ticketleft) / $ticket) * 100;

        $totalBuyArray[$id] = $totalBuy;
      }

      arsort($totalBuyArray);

      $counter = 0;
      foreach ($totalBuyArray as $eventId => $totalBuy) {
        if ($counter >= 3) {
          break;
        }

        $eventDetails = $this->database->getReference('/events/' . $eventId)->getValue();
        $eventDetails["eventId"] = $eventId;
        $featuredEvents[] = $eventDetails;

        $counter++;
      }

      return response()->json(['success' => true, 'featured_events' => $featuredEvents], 200);
    } catch (\Exception $e) {
      return response()->json(['success' => false], 500);
    }
  }
}
