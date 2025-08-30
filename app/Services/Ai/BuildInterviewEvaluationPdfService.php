<?php

namespace App\Services\Ai;

use App\Models\InterviewAnswerEvaluation;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Str;

class BuildInterviewEvaluationPdfService
{
    public function download(InterviewAnswerEvaluation $evaluation)
    {
        $evaluation->loadMissing(['question:id,title,category']);

        $data = [
            'id' => $evaluation->id,
            'answer' => $evaluation->answer,
            'question' => [
                'title' => $evaluation->question?->title,
                'category' => $evaluation->question?->category,
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
            'strengths' => $evaluation->strengths ?? [],
            'priority_areas_for_improvement' => $evaluation->priority_areas_for_improvement ?? [],
            // Clean leading icons like ✅ / ❌ to avoid PDF encoding boxes
            'comparative_analysis' => collect($evaluation->comparative_analysis ?? [])
                ->map(fn ($s) => $this->cleanComparative((string) $s))
                ->all(),
            'encouraging_advice' => $evaluation->encouraging_advice ?? [],
        ];

        $html = view('pdf.interview-evaluation', $data)->render();

        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = 'Interview Evaluation - '.Str::of((string) ($evaluation->question?->title ?? 'Untitled'))
            ->squish()->limit(80)->toString().'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function extractJustification(string $section, ?string $raw): ?string
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

    private function cleanComparative(string $text): string
    {
        // Remove a single leading checkmark or cross (and any following spaces)
        $clean = preg_replace('/^[\x{2705}\x{274C}]\s*/u', '', $text) ?? $text;

        return trim($clean);
    }
}
