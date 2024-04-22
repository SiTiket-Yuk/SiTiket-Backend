<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;

class UserController extends Controller
{
  public function UserData($uid)
  {
      //Input = uid user
      //output = semua info user dalam bentuk array
      try {
          $user = Firebase::database()->getReference('/users/' . $uid)->getValue();
          
          if (!$user ) {
              return response()->json(['success' => false, 'message' => 'Event not found'], 404);
          }
          
          return response()->json(['success' => true, 'user' => $user], 200);
      } catch (\Exception $e) {
          return response()->json(['success' => false, 'message' => 'Internal server error'], 500);
      }
  }
}