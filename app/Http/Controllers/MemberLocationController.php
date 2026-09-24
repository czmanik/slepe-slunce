<?php

namespace App\Http\Controllers;

use App\Models\Expedition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberLocationController extends Controller
{
    public function create(): View
    {
        return view('tracking.location');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'return_to' => ['nullable', 'in:journal'],
        ]);
        unset($data['return_to']);
        $expedition = Expedition::default();
        $request->user()->locations()->updateOrCreate(
            ['expedition_id' => $expedition->getKey()],
            [...$data, 'reported_at' => now()],
        );
        $message = 'Poloha byla odeslána. Na mapě je nyní vidět pouze toto poslední hlášení.';

        return $request->input('return_to') === 'journal'
            ? redirect()->route('posts.index')->with('message', $message)
            : back()->with('message', $message);
    }
}
