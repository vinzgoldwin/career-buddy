<?php

use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\Ai\AiAutoApplyController;
use App\Http\Controllers\Ai\AiJobController;
use App\Http\Controllers\Ai\AiResumeBuilderController;
use App\Http\Controllers\Ai\InterviewQuestionBankController;
use App\Http\Controllers\Ai\InterviewAnswerController;
use App\Http\Controllers\Ai\InterviewAnswerEvaluationController;
use App\Http\Controllers\Ai\MockInterviewController;
use App\Http\Controllers\Ai\ProfileEvaluationController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }

    return Inertia::render('Welcome');
})->name('welcome');

Route::get('/home', function () {
    return Inertia::render('Home');
})->middleware(['auth', 'verified'])->name('home');

Route::get('ai-resume-builder', [AiResumeBuilderController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('ai-resume-builder');

Route::post('ai-resume-builder', [AiResumeBuilderController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('ai-resume-builder.store');

// Dedicated editor view for the AI Resume Builder (non-modal)
Route::get('ai-resume-builder/editor', [AiResumeBuilderController::class, 'editor'])
    ->middleware(['auth', 'verified'])
    ->name('ai-resume-builder.editor');

Route::get('mock-interview', [MockInterviewController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('mock-interview');

Route::get('interview-question-bank', [InterviewQuestionBankController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('interview-question-bank');

Route::get('interview-question-bank/{question}', [InterviewQuestionBankController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('interview-question-bank.show');

Route::post('interview-question-bank/{question}/answer', [InterviewAnswerController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('interview-question-bank.answer.store');

Route::get('interview-answer-evaluations', [InterviewAnswerEvaluationController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('interview-answer-evaluations.index');

Route::get('interview-answer-evaluations/{evaluation}', [InterviewAnswerEvaluationController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('interview-answer-evaluations.show');

Route::get('ai-auto-apply', [AiAutoApplyController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('ai-auto-apply');

Route::get('billing', [BillingController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('billing');

Route::get('affiliate', [AffiliateController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('affiliate');

Route::post('ai-resume-builder/upload', [AiResumeBuilderController::class, 'uploadResume'])
    ->middleware(['auth'])
    ->name('ai-resume-builder.upload');

Route::post('ai-resume-builder/parse-job', [AiJobController::class, 'parse'])
    ->middleware(['auth', 'verified'])
    ->name('ai-resume-builder.parse-job');

Route::get('ai-resume-builder/download', [AiResumeBuilderController::class, 'download'])
    ->middleware(['auth', 'verified'])
    ->name('ai-resume-builder.download');

Route::get('ai-evaluations', [ProfileEvaluationController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('ai-evaluation.index');

Route::get('ai-evaluation/{evaluation}', [ProfileEvaluationController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('ai-evaluation.show');

Route::post('ai-evaluation/{evaluation}/apply-change/{change}', [ProfileEvaluationController::class, 'applyChange'])
    ->middleware(['auth', 'verified'])
    ->name('ai-evaluation.apply-change');

Route::post('ai-evaluation/{evaluation}/apply-all', [ProfileEvaluationController::class, 'applyAll'])
    ->middleware(['auth', 'verified'])
    ->name('ai-evaluation.apply-all');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::post('/pdf/extract-text', [PdfController::class, 'extractText'])
    ->middleware(['auth', 'verified'])
    ->name('pdf.extract-text');
