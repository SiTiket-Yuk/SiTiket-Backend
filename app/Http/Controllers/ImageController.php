<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
  public function __construct()
  {
    parent::__construct();
  }

  public function getImage($id)
  {
    //Input = id (bisa uid user, atau id events)
    //output = gambar
    $id = $id . ".jpg";

    $imageRef = $this->$storage->getBucket()->object("{$id}");

    if (!$imageRef->exists()) {
      return response()->json(['success' => false], 404);
    }

    $downloadUrl = $imageRef->signedUrl(new \DateTime('tomorrow'));

    return $downloadUrl;
  }

  public function getLogo($id)
  {
    //Input = id (bisa uid user, atau id events)
    //output = gambar
    $id = $id . "_org.png";

    $imageRef = $this->$storage->getBucket()->object("{$id}");

    if (!$imageRef->exists()) {
      return response()->json(['success' => false], 404);
    }

    $downloadUrl = $imageRef->signedUrl(new \DateTime('tomorrow'));

    return $downloadUrl;
  }


  public function uploadImage(Request $request)
  {
    //Input = file image, id (bisa uid user, atau id events)
    //output = gambar terdaftar di firebase storage /$id
    $id = $request->input('id');

    if (!$request->hasFile('image')) {
      return response()->json(['success' => false], 400);
    }

    $file = $request->file('image');

    if (!$file->isValid()) {
      return response()->json(['success' => false], 400);
    }

    $imageRef = $this->$storage->getBucket()->upload($file->getPathname(), [
      'name' => "{$id}"
    ]);


    if (!$imageRef) {
      return response()->json(['success' => false], 500);
    }

    return response()->json(['success' => true], 200);
  }
}
