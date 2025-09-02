<?php

namespace App\Http\Controllers\Interview;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\SubmitInterviewAnswerRequest;
use App\Models\InterviewQuestion;
use App\Services\Ai\InterviewAnswerEvaluationService;
use Illuminate\Http\JsonResponse;

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
