<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\InterviewAnswerEvaluation;
use App\Services\Ai\BuildInterviewEvaluationPdfService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InterviewAnswerEvaluationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $evaluations = InterviewAnswerEvaluation::query()
            ->with(['question:id,title,category'])
            ->when($user, fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->paginate(10)
            ->through(function (InterviewAnswerEvaluation $e) {
                $avg = collect([
                    $e->overall_performance_score,
                    $e->structural_integrity_score,
                    $e->content_accuracy_score,
                    $e->fluency_of_expression_score,
                ])->filter(fn ($v) => is_numeric($v))->avg();

                return [
                    'id' => $e->id,
                    'question' => [
                        'id' => $e->question?->id,
                        'title' => $e->question?->title,
                        'category' => $e->question?->category,
                    ],
                    'average_score' => $avg ? round($avg, 1) : null,
                    'overall_performance_score' => $e->overall_performance_score,
                    'created_at' => $e->created_at,
                ];
            });

        return Inertia::render('ai/InterviewAnswerEvaluations', [
            'evaluations' => $evaluations,
        ]);
    }

    public function show(InterviewAnswerEvaluation $evaluation)
    {
        $evaluation->load(['question:id,title,category,explanation']);

        return Inertia::render('ai/InterviewAnswerEvaluation', [
            'evaluation' => [
                'id' => $evaluation->id,
                'answer' => $evaluation->answer,
                'question' => [
                    'id' => $evaluation->question?->id,
                    'title' => $evaluation->question?->title,
                    'category' => $evaluation->question?->category,
                    'explanation' => $evaluation->question?->explanation,
                ],
                'overall_performance' => [
                    'score' => $evaluation->overall_performance_score,
                    'justification' => $this->extractJustification('overall_performance', $evaluation->raw_output),
                ],
                'structural_integrity' => [
                    'score' => $evaluation->structural_integrity_score,
                    'justification' => $this->extractJustification('structural_integrity', $evaluation->raw_output),
                ],
                'content_accuracy' => [
                    'score' => $evaluation->content_accuracy_score,
                    'justification' => $this->extractJustification('content_accuracy', $evaluation->raw_output),
                ],
                'fluency_of_expression' => [
                    'score' => $evaluation->fluency_of_expression_score,
                    'justification' => $this->extractJustification('fluency_of_expression', $evaluation->raw_output),
                ],
                'strengths' => $evaluation->strengths,
                'priority_areas_for_improvement' => $evaluation->priority_areas_for_improvement,
                'comparative_analysis' => $evaluation->comparative_analysis,
                'encouraging_advice' => $evaluation->encouraging_advice,
            ],
        ]);
    }

    public function download(InterviewAnswerEvaluation $evaluation, BuildInterviewEvaluationPdfService $service)
    {
        return $service->download($evaluation);
    }

    protected function extractJustification(string $section, ?string $raw): ?string
    {
        if (! $raw) {
            return null;
        }
        if (preg_match('/<'.preg_quote($section, '/').'>(.*?)<\/'.preg_quote($section, '/').'>/is', $raw, $m)) {
            $text = trim($m[1]);
            $text = preg_replace('/\n?\s*Score:\s*\d{1,2}\s*$/i', '', $text);

            return trim($text);
        }

        return null;
    }
}
