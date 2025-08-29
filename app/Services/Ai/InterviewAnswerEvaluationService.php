<?php

namespace App\Services\Ai;

use App\Models\InterviewAnswerEvaluation;
use Illuminate\Support\Arr;
use OpenAI\Laravel\Facades\OpenAI;

class InterviewAnswerEvaluationService
{
    public function evaluateAndStore(array $question, string $userAnswer, ?int $userId = null): array
    {
        $prompt = $this->buildPrompt($question, $userAnswer);
        $model = config('ai.models.interview_answer_evaluation', 'openai/gpt-oss-120b');
        $maxTokens = (int) config('ai.tokens.interview_answer_evaluation', 4000);

        $resp = OpenAI::chat()->create([
            'model' => $model,
            'temperature' => 0,
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ])->toArray();

        $content = $resp['choices'][0]['message']['content'] ?? '';

        [$parsed, $errors] = $this->parseEvaluation($content);

        $saved = InterviewAnswerEvaluation::create([
            'interview_question_id' => Arr::get($question, 'id'),
            'user_id' => $userId,
            'answer' => $userAnswer,
            'overall_performance_score' => Arr::get($parsed, 'overall_performance.score'),
            'structural_integrity_score' => Arr::get($parsed, 'structural_integrity.score'),
            'content_accuracy_score' => Arr::get($parsed, 'content_accuracy.score'),
            'fluency_of_expression_score' => Arr::get($parsed, 'fluency_of_expression.score'),
            'strengths' => Arr::get($parsed, 'strengths'),
            'priority_areas_for_improvement' => Arr::get($parsed, 'priority_areas_for_improvement'),
            'comparative_analysis' => Arr::get($parsed, 'comparative_analysis'),
            'encouraging_advice' => Arr::get($parsed, 'encouraging_advice'),
            'raw_output' => $content,
            'errors' => $errors,
            'usage' => $resp['usage'] ?? null,
            'llm_model' => $resp['model'] ?? $model,
        ]);

        return [
            'model' => $saved,
            'data' => $parsed,
            'errors' => $errors,
            'raw' => $content,
            'usage' => $resp['usage'] ?? null,
            'llm_model' => $resp['model'] ?? $model,
        ];
    }

    private function buildPrompt(array $question, string $userAnswer): string
    {
        $q = [
            'title' => Arr::get($question, 'title'),
            'category' => Arr::get($question, 'category'),
            'explanation' => Arr::get($question, 'explanation'),
        ];

        $questionJson = json_encode($q, JSON_UNESCAPED_SLASHES);
        $user = $this->sanitizeForPrompt($userAnswer);

        $template = <<<'PROMPT'
            You are an expert evaluator tasked with assessing a user's answer to a given question. You will be provided with a JSON object containing the question details and the user's answer. Your goal is to provide a comprehensive evaluation of the answer, including scores, strengths, areas for improvement, and advice.

            First, you will be presented with the question details in JSON format:

            <question_json>
            {{QUESTION_JSON}}
            </question_json>

            Next, you will see the user's answer:

            <user_answer>
            {{USER_ANSWER}}
            </user_answer>

            Analyze the user's answer in relation to the question provided. Consider the following aspects:

            1. Overall Performance: How well the answer addresses the question as a whole.
            2. Structural Integrity: The organization and logical flow of the answer.
            3. Content Accuracy: The correctness and relevance of the information provided.
            4. Fluency of Expression: The clarity and effectiveness of the language used.

            For each aspect, provide a detailed justification followed by a score on a scale of 1 to 10, where 1 is poor and 10 is excellent.

            After your analysis, create the following sections:

            1. Strengths: Identify 2-3 key strengths of the answer.
            2. Priority Areas for Improvement: Highlight 2-3 specific areas where the user can improve their answer.
            3. Comparative Analysis: Output EXACTLY 3–5 checklist bullets (no paragraphs). Each line must start with:
               - ✅ for aspects where the user's performance meets or exceeds expectations (average or expert),
               - ❌ for aspects where the user's performance falls short.
               Keep each bullet concise and comparative (what the user did vs average vs expert).
            4. Encouraging Advice: Provide 2-3 pieces of constructive and motivating advice for the user to enhance their performance in future responses.

            Your final output should be formatted as follows:

            <evaluation>
            <overall_performance>
            [Justification for overall performance]
            Score: [1-10]
            </overall_performance>

            <structural_integrity>
            [Justification for structural integrity]
            Score: [1-10]
            </structural_integrity>

            <content_accuracy>
            [Justification for content accuracy]
            Score: [1-10]
            </content_accuracy>

            <fluency_of_expression>
            [Justification for fluency of expression]
            Score: [1-10]
            </fluency_of_expression>

            <strengths>
            1. [First strength]
            2. [Second strength]
            3. [Third strength (if applicable)]
            </strengths>

            <priority_areas_for_improvement>
            1. [First area for improvement]
            2. [Second area for improvement]
            3. [Third area for improvement (if applicable)]
            </priority_areas_for_improvement>

            <comparative_analysis>
            ✅ [Concise highlight comparing user vs average/expert]
            ✅ [Concise highlight comparing user vs average/expert]
            ✅ [Concise highlight comparing user vs average/expert]
            ❌ [Concise highlight showing where user falls short]
            [Optional 5th bullet with ✅ or ❌]
            </comparative_analysis>

            <encouraging_advice>
            1. [First piece of advice]
            2. [Second piece of advice]
            3. [Third piece of advice (if applicable)]
            </encouraging_advice>
            </evaluation>

            Ensure that your evaluation is balanced, constructive, and tailored to the specific question and answer provided. Your goal is to provide valuable feedback that will help the user improve their performance in future responses.
        PROMPT;

        return str_replace([
            '{{QUESTION_JSON}}',
            '{{USER_ANSWER}}',
        ], [
            $questionJson,
            $user,
        ], $template);
    }

    private function sanitizeForPrompt(string $text): string
    {
        return trim($text);
    }

    private function parseEvaluation(string $content): array
    {
        $errors = null;
        $getSection = function (string $name) use ($content): ?string {
            if (preg_match('/<' . $name . '>(.*?)<\/' . $name . '>/is', $content, $m)) {
                return trim($m[1]);
            }
            return null;
        };

        $parseScored = function (?string $section): array {
            if ($section === null) {
                return ['justification' => null, 'score' => null];
            }
            $score = null;
            if (preg_match('/Score:\s*(\d{1,2})/i', $section, $sm)) {
                $score = (int) $sm[1];
            }
            $just = trim(preg_replace('/Score:\s*\d{1,2}.*/i', '', $section));
            return ['justification' => $just === '' ? null : $just, 'score' => $score];
        };

        $toArray = function (?string $block): ?array {
            if ($block === null) {
                return null;
            }
            $lines = preg_split('/\r?\n/', trim($block));
            $items = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') { continue; }
                // Keep leading ✅/❌ if present; strip numbering and generic bullets only
                $line = preg_replace('/^(\d+\.|[•\-\*])\s*/u', '', $line);
                $items[] = $line;
            }
            return $items ?: null;
        };

        $data = [
            'overall_performance' => $parseScored($getSection('overall_performance')),
            'structural_integrity' => $parseScored($getSection('structural_integrity')),
            'content_accuracy' => $parseScored($getSection('content_accuracy')),
            'fluency_of_expression' => $parseScored($getSection('fluency_of_expression')),
            'strengths' => $toArray($getSection('strengths')),
            'priority_areas_for_improvement' => $toArray($getSection('priority_areas_for_improvement')),
            'comparative_analysis' => $toArray($getSection('comparative_analysis')),
            'encouraging_advice' => $toArray($getSection('encouraging_advice')),
        ];

        // Basic validation
        if ($data['overall_performance']['score'] === null) {
            $errors = 'Missing or unparseable overall performance score.';
        }

        return [$data, $errors];
    }
}
