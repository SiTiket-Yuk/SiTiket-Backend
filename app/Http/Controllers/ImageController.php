<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
  public function __construct()
  {
    parent::__construct();
  }

  public function GetImage($id)
  {
    //Input = id (bisa uid user, atau id events)
    //output = gambar
    $id = $id . ".jpg";

    $imageRef = $this->storage->getBucket()->object("{$id}");

    if (!$imageRef->exists()) {
      return response()->json(['message' => 'failed'], 404);
    }

    $downloadUrl = $imageRef->signedUrl(new \DateTime('tomorrow'));

    return $downloadUrl;
  }

  public function GetLogo($id)
  {
    //Input = id (bisa uid user, atau id events)
    //output = gambar
    $id = $id . "_org.png";

    $imageRef = $this->storage->getBucket()->object("{$id}");

    if (!$imageRef->exists()) {
      return response()->json(['message' => 'failed'], 404);
    }

    $downloadUrl = $imageRef->signedUrl(new \DateTime('tomorrow'));

    return $downloadUrl;
  }

  public function GetEventAssest($id)
  {
    $postId = $id . ".jpg";
    $logoId = $id . "_org.png";

    $postRef = $this->storage->getBucket()->object("{$postId}");
    $logoRef = $this->storage->getBucket()->object("{$logoId}");

    if (!$postRef->exists() || !$logoRef->exists()) {
      return response()->json(['message' => 'failed'], 404);
    }

    $postUrl = $postRef->signedUrl(new \DateTime('tomorrow'));
    $logoUrl = $logoRef->signedUrl(new \DateTime('tomorrow'));

    return response()->json(['message' => 'success', 'asset' => [$postUrl, $logoUrl]], 200);
  }


  public function uploadImage(Request $request)
  {
    //Input = file image, id (bisa uid user, atau id events)
    //output = gambar terdaftar di firebase storage /$id
    $id = $request->input('id');

    if (!$request->hasFile('image')) {
      return response()->json(['message' => 'failed'], 400);
    }

    $file = $request->file('image');

    if (!$file->isValid()) {
      return response()->json(['success' => 'failed'], 400);
    }

    $imageRef = $this->storage->getBucket()->upload($file->getPathname(), [
      'name' => "{$id}"
    ]);


    if (!$imageRef) {
      return response()->json(['message' => 'failed'], 500);
    }

    return response()->json(['message' => 'success'], 200);
  }
}
