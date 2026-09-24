<?php

namespace App\Http\Controllers;

use App\Models\Expedition;
use App\Models\MapPhoto;
use App\Models\RoutePoint;
use App\Models\RouteSegment;
use App\Services\ExpeditionTracker;
use App\Services\PhotoMetadata;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MapPhotoController extends Controller
{
    public function create(ExpeditionTracker $tracker): View
    {
        return view('tracking.photo', ['position' => $tracker->position()]);
    }

    public function store(Request $request, ExpeditionTracker $tracker, PhotoMetadata $metadata): RedirectResponse
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'max:15360'], 'alt' => ['required', 'string', 'max:300'],
            'caption' => ['nullable', 'string', 'max:500'], 'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'], 'taken_at' => ['nullable', 'date'],
            'return_to' => ['nullable', 'in:journal'],
        ]);
        $embedded = $metadata->location($request->file('image')->getRealPath());
        $fallback = $tracker->position();
        $latitude = $embedded['latitude'] ?? $data['latitude'] ?? $fallback['latitude'] ?? null;
        $longitude = $embedded['longitude'] ?? $data['longitude'] ?? $fallback['longitude'] ?? null;
        if ($latitude === null || $longitude === null) {
            return back()->withErrors(['latitude' => 'Nejdřív určete polohu telefonu nebo vyplňte souřadnice.'])->withInput();
        }
        $active = $tracker->active();
        $expedition = $active?->expedition ?? Expedition::default();
        $storedImage = $request->file('image')->store('map/photos', 'public');
        $metadata->strip(Storage::disk('public')->path($storedImage));
        MapPhoto::query()->create([
            'expedition_id' => $expedition->getKey(),
            'user_id' => $request->user()->id, 'image' => $storedImage,
            'alt' => $data['alt'], 'caption' => $data['caption'] ?? null, 'latitude' => $latitude, 'longitude' => $longitude,
            'taken_at' => $data['taken_at'] ?? now(),
            'route_point_id' => $active instanceof RoutePoint ? $active->id : null,
            'route_segment_id' => $active instanceof RouteSegment ? $active->id : null,
        ]);
        $message = 'Fotografie byla zveřejněna na mapě.';

        return $request->input('return_to') === 'journal'
            ? redirect()->route('posts.index')->with('message', $message)
            : back()->with('message', $message);
    }
}
