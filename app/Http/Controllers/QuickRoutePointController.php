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

        return view('route.quick-create', ['statuses' => RoutePointStatus::options()]);
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
        ]);

        $point = RoutePoint::query()->create([
            ...$data,
            'expedition_id' => Expedition::default()->getKey(),
            'is_goal' => $request->boolean('is_goal'),
            'occurred_at' => now(),
            'route_order' => ((int) RoutePoint::query()->where('expedition_id', Expedition::default()->getKey())->max('route_order')) + 10,
        ]);

        return redirect()
            ->route('route.quick.create')
            ->with('status', "Bod {$point->name} je uložený. Média a článek můžete doplnit v administraci.");
    }
}
