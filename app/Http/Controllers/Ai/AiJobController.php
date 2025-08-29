<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\ParseJobRequest;
use App\Services\Ai\ProfileEvaluationService;
use App\Services\Job\JobDescriptionParserService;
use App\Services\Profile\ProfileJsonService;
use Illuminate\Support\Facades\Auth;

class AiJobController extends Controller
{
    public function parse(
        ParseJobRequest $request,
        JobDescriptionParserService $parser,
        ProfileJsonService $profileService,
        ProfileEvaluationService $evaluationService
    ) {
        set_time_limit(150);
        $raw = (string) $request->input('raw');

        // Parse and persist the job description
        $job = $parser->parseAndStore($raw, Auth::id());
        $jobId = $job['model']?->id ?? null;

        // Build current user's profile
        $profile = $profileService->buildForUser(Auth::user());

        // Evaluate and persist the profile against the job
        $evaluation = $evaluationService->evaluateAndStore(
            $profile,
            $job['data'] ?? [],
            Auth::id(),
            $jobId
        );

        // Non-Inertia requests (API/tests): return JSON
        if (! $request->header('X-Inertia')) {
            return response()->json([
                'job' => $job['data'] ?? [],
                'job_id' => $jobId,
                'job_errors' => $job['errors'] ?? [],
                'profile' => $profile,
                'evaluation' => $evaluation['data'] ?? [],
                'evaluation_id' => $evaluation['model']?->id,
                'evaluation_errors' => $evaluation['errors'] ?? [],
            ]);
        }

        return redirect()->route('ai-evaluation.show', ['evaluation' => $evaluation['model']?->id]);
    }
}
