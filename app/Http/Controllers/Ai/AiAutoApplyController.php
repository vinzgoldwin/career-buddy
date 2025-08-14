<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiAutoApplyController extends Controller
{
    /**
     * Display the AI Auto Apply page.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('ai/AiAutoApply');
    }
}