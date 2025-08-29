<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\InterviewQuestion;
use App\Services\Ai\InterviewAnswerEvaluationService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Ai\SubmitInterviewAnswerRequest;

class InterviewAnswerController extends Controller
{
    public function store(SubmitInterviewAnswerRequest $request, InterviewQuestion $question, InterviewAnswerEvaluationService $service): JsonResponse
    {
        $validated = $request->validated();

        $questionPayload = [
            'id' => $question->id,
            'title' => $question->title,
            'category' => $question->category,
            'explanation' => $question->explanation,
        ];

        $result = $service->evaluateAndStore($questionPayload, $validated['answer'], $request->user()?->id);

        return response()->json([
            'id' => $result['model']->id,
            'data' => $result['data'],
        ]);
    }
}
