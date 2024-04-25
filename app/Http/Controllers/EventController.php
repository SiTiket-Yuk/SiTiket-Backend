<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;

class EventController extends Controller
{
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
                'ticketleft'=> $ticket
            ];
            $id = Firebase::database()->getReference('/events')->push()->getKey();
            Firebase::database()->getReference('/events/' . $id)->set($data);

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
            $event = Firebase::database()->getReference('/events/' . $id)->getValue();
            
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
            $events = Firebase::database()->getReference('/events')->getValue();
            
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
            $event = Firebase::database()->getReference('/events/' . $id)->getValue();
            if (!$event) {
                return response()->json(['success' => false], 404);
            }

            $user = Firebase::database()->getReference('/users/' . $uid)->getValue();
            if (!$user) {
                return response()->json(['success' => false], 404);
            }

            $registeredEvents = Firebase::database()->getReference('/users/' . $uid . '/registeredevents')->getValue();
            if ($registeredEvents !== null){
                $eventNotRegistered = Firebase::database()->getReference('/users/' . $uid . '/registeredevents/' . $id)->getValue();
                if($eventNotRegistered){
                    $eventStatus = Firebase::database()->getReference('/events/' . $id . '/status')->getValue();
                    if($eventStatus === 'open'){
                        
                        $ticketleft = Firebase::database()->getReference('/events/' . $id . '/ticketleft')->getValue();
                        if ($ticketleft - $ticketBuy < 0){
                            return response()->json(['success' => false], 200);
                        }
                        $ticketleft = $ticketleft - $ticketBuy;
                        Firebase::database()->getReference('/events/' . $id . '/ticketleft')->set($ticketleft);
                        if($ticketleft === 0){
                            Firebase::database()->getReference('/events/' . $id . '/status')->set("closed");
                        }
                        $harga = Firebase::database()->getReference('/events/' . $id . '/price')->getValue();
                        $harga = $harga * $ticketBuy;

                        Firebase::database()->getReference('/users/' . $uid . '/registeredevents/' . $id . "/status")->set("Belum Lunas");
                        Firebase::database()->getReference('/users/' . $uid . '/registeredevents/' . $id . "/ticket")->set($ticketBuy);
                        Firebase::database()->getReference('/users/' . $uid . '/registeredevents/' . $id . "/total")->set($harga);
                        Firebase::database()->getReference('/users/' . $uid . '/registeredevents/' . $id . "/timelimit")->set($timelimit);
                        
                    }
                }else{
                    return response()->json(['success' => false], 400);
                }
                
            }else{
                $userRef = Firebase::database()->getReference('/users/' . $uid . '/registeredevents/' . 0);
                $userRef->set($id);
            }
            
            
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    public function Lunas($id, $uid){
        try {
            Firebase::database()->getReference('/users/' . $uid . '/registeredevents/' . $id)->set("Lunas");
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    public function FeaturedEvent(){
        try {
            $events = Firebase::database()->getReference('/events')->getValue();
            $featuredEvents = [];
    
            $totalBuyArray = [];
    
            foreach ($events as $id => $event) {
                $ticketStatus = Firebase::database()->getReference('/events/' . $id . '/status')->getValue();

                if ($ticketStatus === 'closed') {
                    continue;
                }
                $eventDate = Firebase::database()->getReference('/events/' . $id . '/date')->getValue();

                if (strtotime($eventDate) <= strtotime(date('Y-m-d'))) {
                    continue;
                }

                $ticket = Firebase::database()->getReference('/events/' . $id . '/ticket')->getValue();
                $ticketleft = Firebase::database()->getReference('/events/' . $id . '/ticketleft')->getValue();
    
                $totalBuy = (($ticket - $ticketleft) / $ticket) * 100;
    
                $totalBuyArray[$id] = $totalBuy;
            }
    
            arsort($totalBuyArray);
    
            $counter = 0;
            foreach ($totalBuyArray as $eventId => $totalBuy) {
                if ($counter >= 3) {
                    break;
                }

                $eventDetails = Firebase::database()->getReference('/events/' . $eventId)->getValue();
                $featuredEvents[] = $eventDetails;

                $counter++;
            }
    
            return response()->json(['success' => true, 'featured_events' => $featuredEvents], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
}