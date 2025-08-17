<?php

use App\Http\Controllers\Ai\AiAutoApplyController;
use App\Http\Controllers\Ai\AiResumeBuilderController;
use App\Http\Controllers\Ai\InterviewQuestionBankController;
use App\Http\Controllers\Ai\MockInterviewController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('ai-resume-builder', [AiResumeBuilderController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('ai-resume-builder');

Route::post('ai-resume-builder', [AiResumeBuilderController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('ai-resume-builder.store');

Route::get('mock-interview', [MockInterviewController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('mock-interview');

Route::get('interview-question-bank', [InterviewQuestionBankController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('interview-question-bank');

Route::get('ai-auto-apply', [AiAutoApplyController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('ai-auto-apply');

Route::post('ai-resume-builder/upload', [AiResumeBuilderController::class, 'uploadResume'])
    ->middleware(['auth', 'verified'])
    ->name('ai-resume-builder.upload');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::post('/pdf/extract-text', [PdfController::class, 'extractText'])
    ->middleware(['auth', 'verified'])
    ->name('pdf.extract-text');
