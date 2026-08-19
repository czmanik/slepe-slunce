<?php

namespace App\Http\Controllers;

use App\Models\Expedition;
use Illuminate\View\View;

class ExpeditionController extends Controller
{
    public function index(): View
    {
        $expeditions = Expedition::published()->orderByDesc('start_at')->get();

        return view('expeditions.index', compact('expeditions'));
    }

    public function show(Expedition $expedition): View
    {
        abort_unless($expedition->publication_status === 'published', 404);
        $expedition->load([
            'programItems' => fn ($query) => $query->where('is_public', true)->ordered(),
            'members',
            'posts' => fn ($query) => $query->publiclyVisible()->with('authors')->latest('published_at')->limit(3),
        ]);

        return view('expeditions.show', compact('expedition'));
    }
}
