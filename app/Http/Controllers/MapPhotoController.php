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
        $expeditions = Expedition::query()->published()->orderByDesc('start_at')->get();
        $expedition = $expeditions->firstWhere('id', request()->integer('expedition_id')) ?? Expedition::default();

        return view('tracking.photo', ['position' => $tracker->position(expedition: $expedition), 'expeditions' => $expeditions, 'selectedExpedition' => $expedition]);
    }

    public function store(Request $request, ExpeditionTracker $tracker, PhotoMetadata $metadata): RedirectResponse
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'max:15360'], 'alt' => ['required', 'string', 'max:300'],
            'caption' => ['nullable', 'string', 'max:500'], 'short_story' => ['nullable', 'string', 'max:280'], 'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'], 'taken_at' => ['nullable', 'date'],
            'return_to' => ['nullable', 'in:journal'],
            'expedition_id' => ['nullable', 'exists:expeditions,id'],
        ]);
        $expedition = isset($data['expedition_id']) ? Expedition::query()->published()->findOrFail($data['expedition_id']) : Expedition::default();
        $embedded = $metadata->location($request->file('image')->getRealPath());
        $fallback = $tracker->position(expedition: $expedition);
        $latitude = $embedded['latitude'] ?? $data['latitude'] ?? $fallback['latitude'] ?? null;
        $longitude = $embedded['longitude'] ?? $data['longitude'] ?? $fallback['longitude'] ?? null;
        if ($latitude === null || $longitude === null) {
            return back()->withErrors(['latitude' => 'Nejdřív určete polohu telefonu nebo vyplňte souřadnice.'])->withInput();
        }
        $active = $tracker->active(expedition: $expedition);
        $storedImage = $request->file('image')->store('map/photos', 'public');
        $metadata->strip(Storage::disk('public')->path($storedImage));
        MapPhoto::query()->create([
            'expedition_id' => $expedition->getKey(),
            'user_id' => $request->user()->id, 'image' => $storedImage,
            'alt' => $data['alt'], 'caption' => $data['caption'] ?? null, 'short_story' => $data['short_story'] ?? null, 'latitude' => $latitude, 'longitude' => $longitude,
            'taken_at' => $data['taken_at'] ?? now(),
            'route_point_id' => $active instanceof RoutePoint ? $active->id : null,
            'route_segment_id' => $active instanceof RouteSegment ? $active->id : null,
        ]);
        $message = 'Fotografie byla zveřejněna na mapě.';

        return $request->input('return_to') === 'journal'
            ? redirect()->route($request->boolean('journal_expedition') ? 'expeditions.posts' : 'posts.index',
                $request->boolean('journal_expedition') ? [$expedition] : [])->with('message', $message)
            : back()->with('message', $message);
    }
}
