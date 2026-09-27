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
        return view('tracking.location', ['expeditions' => Expedition::query()->published()->orderByDesc('start_at')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'expedition_id' => ['nullable', 'exists:expeditions,id'],
            'return_to' => ['nullable', 'in:journal'],
        ]);
        unset($data['return_to']);
        $expedition = isset($data['expedition_id']) ? Expedition::query()->published()->findOrFail($data['expedition_id']) : Expedition::default();
        $request->user()->locations()->create([...$data, 'expedition_id' => $expedition->getKey(), 'reported_at' => now()]);
        $message = 'Poloha byla uložena k expedici '.$expedition->name.'.';

        return $request->input('return_to') === 'journal'
            ? redirect()->route($request->boolean('journal_expedition') ? 'expeditions.posts' : 'posts.index',
                $request->boolean('journal_expedition') ? [$expedition] : [])->with('message', $message)
            : back()->with('message', $message);
    }
}
