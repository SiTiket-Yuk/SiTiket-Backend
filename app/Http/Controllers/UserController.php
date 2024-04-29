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
        return response()->json(['success' => false, 'message' => 'Event not found'], 404);
      }

      return response()->json(['success' => true, 'user' => $user], 200);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Internal server error'], 500);
    }
  }


  public function getUidwithEmail($email)
  {
    try {
      $user = $this->firebaseAuth->getUserByEmail($email);

      $uid = $user->uid;

      return response()->json(['success' => true, 'uid' => $uid], 200);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
  }
}
