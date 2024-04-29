<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegisterController extends Controller
{

  public function __construct()
  {
    parent::__construct();
  }

  public function register(Request $request)
  {
    // Validation
    $validator = $request->validate([
      'username' => 'required',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|min:8',
    ]);

    $username = $request->input('username');
    $email = $request->input('email');
    $password = $request->input('password');

    try {
      $createdUser = $this->firebaseAuth->createUserWithEmailAndPassword($email, $password);
      $uid = $createdUser->uid;
      $this->database->getReference('/users/' . $uid . '/name')->set($username);

      return response()->json(['success' => true, 'uid' => $uid], 200);
    } catch (\Exception $e) {
      if ($e->getMessage() === 'The email address is already in use by another account.') {
        return response()->json(['success' => false], 400);
      } else {
        return response()->json(['success' => false], 500);
      }
    }
  }
}
