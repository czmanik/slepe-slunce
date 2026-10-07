<?php

namespace App\Http\Controllers;

use App\Models\MapPhoto;
use App\Services\ImageThumbnail;
use Illuminate\View\View;

class PublicPhotoController extends Controller
{
    public function __invoke(MapPhoto $photo, ImageThumbnail $thumbnails): View
    {
        abort_unless($photo->expedition()->published()->exists(), 404);

        return view('map.photo', ['photo' => $photo->load('expedition'), 'displayImage' => $thumbnails->url($photo->image, 'medium')]);
    }
}
