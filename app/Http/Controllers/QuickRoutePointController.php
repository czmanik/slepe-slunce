<?php

namespace App\Http\Controllers;

use App\Enums\RoutePointStatus;
use App\Models\Expedition;
use App\Models\RoutePoint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuickRoutePointController extends Controller
{
    public function create(Request $request): View
    {
        abort_unless($request->user()?->canPublish(), 403);

        return view('route.quick-create', ['statuses' => RoutePointStatus::options(), 'expeditions' => Expedition::query()->published()->orderByDesc('start_at')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canPublish(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:700'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'status' => ['required', 'in:'.implode(',', array_keys(RoutePointStatus::options()))],
            'is_goal' => ['nullable', 'boolean'],
            'expedition_id' => ['nullable', 'exists:expeditions,id'],
        ]);
        $expedition = isset($data['expedition_id']) ? Expedition::query()->published()->findOrFail($data['expedition_id']) : Expedition::default();

        $point = RoutePoint::query()->create([
            ...$data,
            'expedition_id' => $expedition->getKey(),
            'is_goal' => $request->boolean('is_goal'),
            'occurred_at' => now(),
            'route_order' => ((int) RoutePoint::query()->whereBelongsTo($expedition)->max('route_order')) + 10,
        ]);

        return redirect()
            ->route('route.quick.create', ['expedition_id' => $expedition->getKey()])
            ->with('status', "Bod {$point->name} je uložený. Média a článek můžete doplnit v administraci.");
    }
}
