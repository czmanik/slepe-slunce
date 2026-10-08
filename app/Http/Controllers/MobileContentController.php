<?php

namespace App\Http\Controllers;

use App\Enums\RoutePointStatus;
use App\Models\Expedition;
use App\Models\MapPhoto;
use App\Models\RoutePoint;
use App\Services\PhotoMetadata;
use App\Services\ImageThumbnail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MobileContentController extends Controller
{
    public function index(Request $request): View
    {
        $photos = MapPhoto::query()->with('expedition')->when(! $request->user()->canPublish(),
            fn ($query) => $query->where('user_id', $request->user()->id))->latest('taken_at')->limit(50)->get();
        $points = $request->user()->canPublish()
            ? RoutePoint::query()->with('expedition')->latest('occurred_at')->limit(50)->get()
            : collect();

        return view('tracking.manage', compact('photos', 'points'));
    }

    public function editPhoto(Request $request, MapPhoto $photo): View
    {
        abort_unless($request->user()->can('update', $photo), 403);

        return view('tracking.edit-photo', ['photo' => $photo, 'expeditions' => Expedition::published()->orderByDesc('start_at')->get()]);
    }

    public function updatePhoto(Request $request, MapPhoto $photo, PhotoMetadata $metadata, ImageThumbnail $thumbnails): RedirectResponse
    {
        abort_unless($request->user()->can('update', $photo), 403);
        $data = $request->validate([
            'expedition_id' => ['required', 'exists:expeditions,id'], 'image' => ['nullable', 'image', 'max:5120'],
            'alt' => ['required', 'string', 'max:300'], 'caption' => ['nullable', 'string', 'max:500'],
            'short_story' => ['nullable', 'string', 'max:280'], 'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'], 'taken_at' => ['required', 'date'],
        ]);
        Expedition::published()->findOrFail($data['expedition_id']);
        $oldImage = $photo->image;
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('map/photos', 'public');
            $metadata->strip(Storage::disk('public')->path($data['image']));
        }
        if ($photo->expedition_id !== (int) $data['expedition_id']) {
            $data['route_point_id'] = null;
            $data['route_segment_id'] = null;
        }
        try {
            $photo->update($data);
        } catch (\Throwable $error) {
            if (isset($data['image'])) {
                $thumbnails->delete($data['image']);
                Storage::disk('public')->delete($data['image']);
            }
            throw $error;
        }
        if (isset($data['image'])) {
            $thumbnails->delete($oldImage);
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('mobile.content.index')->with('message', 'Fotografie byla upravena.');
    }

    public function editPoint(Request $request, RoutePoint $point): View
    {
        abort_unless($request->user()->can('update', $point), 403);

        return view('tracking.edit-point', [
            'point' => $point, 'expeditions' => Expedition::published()->orderByDesc('start_at')->get(),
            'statuses' => RoutePointStatus::options(),
        ]);
    }

    public function updatePoint(Request $request, RoutePoint $point): RedirectResponse
    {
        abort_unless($request->user()->can('update', $point), 403);
        $data = $request->validate([
            'expedition_id' => ['required', 'exists:expeditions,id'], 'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:700'], 'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'], 'occurred_at' => ['required', 'date'],
            'status' => ['required', 'in:'.implode(',', array_keys(RoutePointStatus::options()))],
            'is_goal' => ['nullable', 'boolean'],
        ]);
        Expedition::published()->findOrFail($data['expedition_id']);
        $moving = $point->expedition_id !== (int) $data['expedition_id'];
        if ($moving && ($point->incomingSegments()->exists() || $point->outgoingSegments()->exists())) {
            throw ValidationException::withMessages(['expedition_id' => 'Místo je součástí trasy. Nejprve upravte navázané úseky v administraci.']);
        }
        $data['is_goal'] = $request->boolean('is_goal');
        DB::transaction(function () use ($point, $data, $moving): void {
            if ($moving) {
                $point->post_id = null;
                $point->location_id = null;
                MapPhoto::query()->where('route_point_id', $point->id)->update(['route_point_id' => null]);
            }
            $point->fill($data)->save();
        });

        return redirect()->route('mobile.content.index')->with('message', 'Místo bylo upraveno.');
    }
}
