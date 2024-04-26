<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;

class LoginController extends Controller
{
  private $firebaseAuth;

  public function __construct()
  {
    $this->firebaseAuth = Firebase::auth();
  }

  public function login(Request $request)
  {

    $validator = $request->validate([
      'email' => 'required|email',
      'password' => 'required|min:8',
    ]);

    try {
      $SignInResult = $this->firebaseAuth->signInWithEmailAndPassword($request->email, $request->password);
      $user = $SignInResult->data();
      $userID = $user['localId'];

      return response()->json(['success' => true, 'uid' => $userID], 200);
    } catch (\Exception $e) {
      return response()->json(['success' => false], 400);
    }
  }
}
