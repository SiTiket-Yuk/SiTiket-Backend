<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;

class UserController extends Controller
{
  private $database;

  public function __construct(Database $database)
  {
    $this->database = $database;
  }

  public function create(Request $request)
  {
    $this->database
      ->getReference('test/blogs/' . $request['title'])
      ->set([
        'title' => $request['title'],
        'content' => $request['content']
      ]);

    return response()->json('blog has been created');
  }

  public function index()
  {
    $reference = $this->database->getReference();
    $value = $reference->getValue();
    return response()->json($value);
  }

  public function edit(Request $request)
  {
    $this->database->getReference('test/blogs/' . $request['title'])
      ->update([
        'content/' => $request['content']
      ]);

    return response()->json('blog has been edited');
  }

  public function delete(Request $request)
  {
    $this->database
      ->getReference('test/blogs/' . $request['title'])
      ->remove();

    return response()->json('blog has been deleted');
  }
}
