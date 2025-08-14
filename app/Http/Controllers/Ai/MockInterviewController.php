<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MockInterviewController extends Controller
{
    /**
     * Display the Mock Interview page.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('ai/MockInterview');
    }
}