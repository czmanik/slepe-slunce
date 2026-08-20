<?php

namespace App\Http\Controllers;

use App\Models\Expedition;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(?Expedition $expedition = null): View
    {
        $expedition ??= Expedition::default();
        $members = $expedition->members()->get();

        return view('members.index', compact('members', 'expedition'));
    }
}
