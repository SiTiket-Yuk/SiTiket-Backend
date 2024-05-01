<?php

namespace App\Http\Controllers;

class UserController extends Controller
{
  public function __construct()
  {
    parent::__construct();
  }

  public function UserData($uid)
  {
    //Input = uid user
    //output = semua info user dalam bentuk array
    try {
      $user = $this->database->getReference('/users/' . $uid)->getValue();

      if (!$user) {
        return response()->json(['message' => 'Event not found'], 404);
      }

      return response()->json(['message' => 'success', 'user' => $user], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Internal server error'], 500);
    }
  }


  public function GetUidwithEmail($email)
  {
    try {
      $user = $this->firebaseAuth->getUserByEmail($email);

      $uid = $user->uid;

      return response()->json(['message' => 'success', 'uid' => $uid], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Internal server error', 'error' => $e->getMessage()], 500);
    }
  }
}
