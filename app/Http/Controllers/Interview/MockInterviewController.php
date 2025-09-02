<?php

namespace App\Http\Controllers\Interview;

use App\Http\Controllers\Controller;
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
        return Inertia::render('interview/MockInterview');
    }
}
