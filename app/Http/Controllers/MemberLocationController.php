<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberLocationController extends Controller
{
    public function create(): View { return view('tracking.location'); }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);
        $request->user()->location()->updateOrCreate([], [...$data, 'reported_at' => now()]);
        return back()->with('message', 'Poloha byla odeslána. Na mapě je nyní vidět pouze toto poslední hlášení.');
    }
}
