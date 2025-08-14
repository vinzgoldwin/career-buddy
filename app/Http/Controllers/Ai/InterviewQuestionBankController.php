<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InterviewQuestionBankController extends Controller
{
    /**
     * Display the Interview Question Bank page.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('ai/InterviewQuestionBank');
    }
}