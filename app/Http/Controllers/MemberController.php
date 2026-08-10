<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(): View
    {
        $members = Author::query()->where('is_expedition_member', true)->orderBy('sort_order')->orderBy('name')->get();
        return view('members.index', compact('members'));
    }
}
