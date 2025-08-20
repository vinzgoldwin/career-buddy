<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\ParseJobRequest;
use App\Services\Job\JobDescriptionParserService;
use App\Services\Profile\ProfileJsonService;
use Illuminate\Support\Facades\Auth;

class AiJobController extends Controller
{
    public function parse(
        ParseJobRequest $request,
        JobDescriptionParserService $parser,
        ProfileJsonService $profileService
    ) {
        $raw = (string) $request->input('raw');

        $job = $parser->parse($raw);
        $profile = $profileService->buildForUser(Auth::user());
        dd($job);

        // Non-Inertia requests (API/tests): return JSON
        if (! $request->header('X-Inertia')) {
            return response()->json([
                'job' => $job,
                'profile' => $profile,
            ]);
        }

        // Inertia flow: flash and redirect back or to builder
        return redirect()
            ->route('ai-resume-builder')
            ->with('job_parsed', $job)
            ->with('profile_json', $profile);
    }
}
