<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
  public function __construct()
  {
    parent::__construct();
  }

  public function Register(Request $request)
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

      return response()->json(['message' => 'success', 'uid' => $uid], 200);
    } catch (\Exception $e) {
      if ($e->getMessage() === 'The email address is already in use by another account.') {
        return response()->json(['message' => 'email is already exist'], 400);
      } else {
        return response()->json(['message' => 'Internal server error'], 500);
      }
    }
  }

  public function Login(Request $request)
  {

    $validator = $request->validate([
      'email' => 'required|email',
      'password' => 'required|min:8',
    ]);

    try {
      $SignInResult = $this->firebaseAuth->signInWithEmailAndPassword($request->email, $request->password);
      $user = $SignInResult->data();
      $userID = $user['localId'];

      return response()->json(['message' => 'success', 'uid' => $userID], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'failed'], 400);
    }
  }
}
