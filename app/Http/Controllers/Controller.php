<?php

namespace App\Http\Controllers;

use Kreait\Laravel\Firebase\Facades\Firebase;

abstract class Controller
{
    protected $firebaseAuth;
    public $storage;
    public $database;

    public function __construct()
    {
        $this->firebaseAuth = Firebase::auth();
        $this->storage = Firebase::storage();
        $this->database = Firebase::database();
    }


}