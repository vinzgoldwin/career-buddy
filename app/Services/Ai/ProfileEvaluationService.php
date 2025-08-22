<?php

namespace App\Services\Ai;

use App\Models\ProfileEvaluation;
use App\Models\ProfileEvaluationImpact;
use App\Models\ProfileEvaluationSkillsAndTraits;
use App\Models\ProfileEvaluationAlignmentWithJob;
use App\Models\ProfileEvaluationSpecificChange;
use Illuminate\Support\Arr;
use OpenAI\Laravel\Facades\OpenAI;

class ProfileEvaluationService
{
    /**
     * Evaluate a profile against a job using the supplied prompt.
     * Returns saved model and parsed payload.
     */
    public function evaluateAndStore(array $profile, array $job, ?int $userId = null, ?int $jobDescriptionId = null): array
    {
        $prompt = $this->buildPrompt($profile, $job);
        $model = config('ai.models.profile_evaluation', 'openai/gpt-oss-120b');
        $maxTokens = (int) config('ai.tokens.profile_evaluation', 5000);

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

        // Extract strengths and areas for improvement from the parsed data
        $strengths = null;
        $areasForImprovement = null;
        
        if (isset($parsed['overall']['improvements'])) {
            $improvements = $parsed['overall']['improvements'];
            
            // Extract strengths using regex
            if (preg_match('/<strengths>(.*?)<\/strengths>/is', $improvements, $strengthsMatch)) {
                $strengths = trim($strengthsMatch[1]);
                // Remove <br/> tags and clean up
                $strengths = str_replace(['<br/>', '<br />', '<br>'], "\n", $strengths);
                $strengths = strip_tags($strengths);
                // Remove bullet points and clean up
                $strengths = preg_replace('/^[\s•\-]+/m', '', $strengths);
                $strengths = trim($strengths);
            }
            
            // Extract areas for improvement using regex
            if (preg_match('/<areas_for_improvement>(.*?)<\/areas_for_improvement>/is', $improvements, $areasMatch)) {
                $areasForImprovement = trim($areasMatch[1]);
                // Remove <br/> tags and clean up
                $areasForImprovement = str_replace(['<br/>', '<br />', '<br>'], "\n", $areasForImprovement);
                $areasForImprovement = strip_tags($areasForImprovement);
                // Remove bullet points and clean up
                $areasForImprovement = preg_replace('/^[\s•\-]+/m', '', $areasForImprovement);
                $areasForImprovement = trim($areasForImprovement);
            }
        }

        $saved = ProfileEvaluation::create([
            'user_id' => $userId,
            'job_description_id' => $jobDescriptionId,
            'total_score' => Arr::get($parsed, 'overall.total_score'),
            'overall_recommendation' => Arr::get($parsed, 'overall.recommendation'),
            'improvements' => Arr::get($parsed, 'overall.improvements'),
            'strengths' => $strengths,
            'areas_for_improvement' => $areasForImprovement,
            'raw_output' => $content,
            'errors' => $errors,
            'usage' => $resp['usage'] ?? null,
            'llm_model' => $resp['model'] ?? $model,
        ]);

        // Create impact evaluation
        if (isset($parsed['impact'])) {
            $impactData = $parsed['impact'];
            ProfileEvaluationImpact::create([
                'profile_evaluation_id' => $saved->id,
                'quantifying_impact_score' => $impactData['quantifying_impact']['score'] ?? null,
                'quantifying_impact_feedback' => $impactData['quantifying_impact']['feedback'] ?? null,
                'focus_on_achievements_score' => $impactData['focus_on_achievements']['score'] ?? null,
                'focus_on_achievements_feedback' => $impactData['focus_on_achievements']['feedback'] ?? null,
                'writing_quality_score' => $impactData['writing_quality']['score'] ?? null,
                'writing_quality_feedback' => $impactData['writing_quality']['feedback'] ?? null,
                'varied_industry_specific_verbs_score' => $impactData['varied_industry_specific_verbs']['score'] ?? null,
                'varied_industry_specific_verbs_feedback' => $impactData['varied_industry_specific_verbs']['feedback'] ?? null,
            ]);
        }

        // Create skills and traits evaluation
        if (isset($parsed['skills_and_traits'])) {
            $skillsData = $parsed['skills_and_traits'];
            ProfileEvaluationSkillsAndTraits::create([
                'profile_evaluation_id' => $saved->id,
                'problem_solving_score' => $skillsData['problem_solving']['score'] ?? null,
                'problem_solving_feedback' => $skillsData['problem_solving']['feedback'] ?? null,
                'communication_collaboration_score' => $skillsData['communication_collaboration']['score'] ?? null,
                'communication_collaboration_feedback' => $skillsData['communication_collaboration']['feedback'] ?? null,
                'initiative_innovation_score' => $skillsData['initiative_innovation']['score'] ?? null,
                'initiative_innovation_feedback' => $skillsData['initiative_innovation']['feedback'] ?? null,
                'leadership_teamwork_score' => $skillsData['leadership_teamwork']['score'] ?? null,
                'leadership_teamwork_feedback' => $skillsData['leadership_teamwork']['feedback'] ?? null,
            ]);
        }

        // Create alignment with job evaluation
        if (isset($parsed['alignment_with_job'])) {
            $alignmentData = $parsed['alignment_with_job'];
            ProfileEvaluationAlignmentWithJob::create([
                'profile_evaluation_id' => $saved->id,
                'skills_match_score' => $alignmentData['skills_match']['score'] ?? null,
                'skills_match_feedback' => $alignmentData['skills_match']['feedback'] ?? null,
                'job_title_match_score' => $alignmentData['job_title_match']['score'] ?? null,
                'job_title_match_feedback' => $alignmentData['job_title_match']['feedback'] ?? null,
                'responsibilities_qualifications_score' => $alignmentData['responsibilities_qualifications']['score'] ?? null,
                'responsibilities_qualifications_feedback' => $alignmentData['responsibilities_qualifications']['feedback'] ?? null,
                'industry_keywords_synonyms_score' => $alignmentData['industry_keywords_synonyms']['score'] ?? null,
                'industry_keywords_synonyms_feedback' => $alignmentData['industry_keywords_synonyms']['feedback'] ?? null,
            ]);
        }

        // Create specific changes
        if (isset($parsed['specific_changes']) && is_array($parsed['specific_changes'])) {
            foreach ($parsed['specific_changes'] as $change) {
                // Convert array values to JSON strings if needed
                $oldValue = $change['old_value'] ?? null;
                $newValue = $change['new_value'] ?? null;
                
                // Ensure old_value and new_value are strings
                if (is_array($oldValue)) {
                    $oldValue = json_encode($oldValue);
                } elseif (!is_string($oldValue) && !is_null($oldValue)) {
                    $oldValue = (string) $oldValue;
                }
                
                if (is_array($newValue)) {
                    $newValue = json_encode($newValue);
                } elseif (!is_string($newValue) && !is_null($newValue)) {
                    $newValue = (string) $newValue;
                }

                ProfileEvaluationSpecificChange::create([
                    'profile_evaluation_id' => $saved->id,
                    'field' => $change['field'] ?? null,
                    'entity_id' => $change['id'] ?? null,
                    'specific_field' => $change['specific_field'] ?? null,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                ]);
            }
        }

        return [
            'model' => $saved,
            'data' => $parsed,
            'errors' => $errors,
            'raw' => $content,
            'usage' => $resp['usage'] ?? null,
            'llm_model' => $resp['model'] ?? $model,
        ];
    }

    private function buildPrompt(array $profile, array $job): string
    {
        $profileJson = json_encode($profile, JSON_UNESCAPED_SLASHES);
        $jobJson = json_encode($job, JSON_UNESCAPED_SLASHES);

        $template = <<<'PROMPT'
            You are an expert in resume evaluation. Your task is to analyze a given profile, score it based on specific criteria, provide detailed feedback, and suggest improvements that will help the candidate secure the desired job. You must strictly base your assessment on the content of the profile and job description. Do not infer anything from demographic details such as names, pronouns, race, gender, age, or other protected characteristics, and do not let them influence scores or feedback.

            1. Here is the profile you will be evaluating:
            <profile_json>
            {{PROFILE_JSON}}
            </profile_json>

            2. Consider the following job description to evaluate the profile’s alignment with the desired position:
            <job_json>
            {{JOB_JSON}}
            </job_json>

            3. Carefully analyze the provided profile data. Pay attention to the following sections: summary, skills (array of {name, proficiency_level}), experiences, education, projects, and certifications. Focus your evaluation on explicit, job‑relevant information; do not guess or assume.

            4. Evaluate the profile based on the following criteria:

            A. Impact (Total Score: 40)
            - Quantifying Impact (10 points): Look for numbers and metrics that quantify accomplishments (e.g., “increased revenue by 30%” or “reduced customer wait time by 50%”). Award full points when most bullets include measurable results.
            - Focus on Achievements (10 points): Highlight accomplishments rather than listing duties. Evidence of results matters more than responsibilities.
            - Writing Quality (12 points): No spelling or grammar errors and correct verb tenses. Bullets should start with strong active verbs; avoid passive constructions and weak phrasing.
            - Varied and Industry‑Specific Verbs (8 points): Use varied, descriptive action verbs that are relevant to the industry and role. Do not repeat the same verb in multiple bullets.

            B. Evidence of Skills & Professional Traits (Total Score: 20)
            - Problem‑Solving and Analytical Ability (5 points): Does the profile describe specific analytical work or problem‑solving results?
            - Communication & Collaboration (5 points): Are there examples of clear communication, teamwork, or collaboration outcomes?
            - Initiative & Innovation (5 points): Does the candidate take proactive steps, introduce improvements, or innovate without prompting?
            - Leadership & Teamwork (5 points): Evidence of leading teams or projects and motivating others.

            C. Alignment with Job (ATS) Keywords (Total Score: 40)
            - Skills Match (10 points): Do the profile’s skills explicitly match the required skills in the job description?
            - Job Title Match (10 points): Are the candidate’s past roles and titles closely related to the target job title?
            - Responsibilities & Qualifications (10 points): Does the profile contain keywords and phrases from the “Responsibilities” and “Qualifications” sections of the job description?
            - Industry Keywords & Synonyms (10 points): Does the profile use industry‑specific terms and synonyms that align with the job description and will help in ATS searches?

            5. For each sub‑component, do the following:
               a. Provide the awarded score (an integer) in the `<score>` tag.
               b. Provide specific feedback explaining how the profile aligns or misaligns with the criteria and what improvements are needed.
               c. Justify the score using concrete examples from the profile (e.g., mention the metrics used, specific action verbs, or missing keywords).

            6. After evaluating all components, provide an overall recommendation:
               a. Calculate the total score from the combined sub‑component scores.
               b. Summarize strengths and areas for improvement, emphasizing alignment with the job. Include actionable advice—both immediate (e.g., “add quantifiable results to role X”) and longer term (e.g., “gain experience with skill Y”).
               c. Format the improvements section with <strengths> and <areas_for_improvement> tags, each containing bullet points.

            7. Suggest specific immediate changes:
               a. Identify exact words, phrases, sentences, or sections of the profile to modify.
               b. Provide clear, actionable modifications for these sections.
               c. Recommend changes in **JSON array** format. Each object must contain:
                  - `field`: one of the valid fields (summary, skills, experiences.*.description, educations.*.description, projects.*.description, or licenses_and_certifications.*.description). Do not use wildcard patterns in your final output.
                  - `id`: the unique identifier of the parent entity if available (use the profile’s `id`, `name`, or zero‑based `index` as a stable locator).
                  - `specific_field`: the nested field name (e.g., "description") when modifying a nested entity.
                  - `old_value`: the current value.
                  - `new_value`: the suggested value.
               d. If there are no specific immediate changes to recommend, leave `<specific_change>` empty.

            8. Guidelines:
               - Fairness: Exclude all demographic or personal identity factors from consideration; score solely on job‑relevant content.
               - Alignment: Keep feedback and scoring tied to the target role and job description.
               - Consistency: Apply the same standards across all profiles.
               - Relevance: Base evaluation strictly on the profile, job JSON, and criteria.
               - Depth & Clarity: Provide detailed, clear reasoning and concrete examples for every point.
               - Accuracy: Ensure scores reflect the evidence cited and avoid over‑ or under‑scoring.
               - Actionable Feedback: Focus on high‑impact, practical advice. Do not suggest changes when none are needed.

            9. Format your output exactly as shown below:

            <evaluation>
            <impact>
            <quantifying_impact>
            <score></score>
            <feedback></feedback>
            </quantifying_impact>
            <focus_on_achievements>
            <score></score>
            <feedback></feedback>
            </focus_on_achievements>
            <writing_quality>
            <score></score>
            <feedback></feedback>
            </writing_quality>
            <varied_industry_specific_verbs>
            <score></score>
            <feedback></feedback>
            </varied_industry_specific_verbs>
            </impact>

            <skills_and_traits>
            <problem_solving>
            <score></score>
            <feedback></feedback>
            </problem_solving>
            <communication_collaboration>
            <score></score>
            <feedback></feedback>
            </communication_collaboration>
            <initiative_innovation>
            <score></score>
            <feedback></feedback>
            </initiative_innovation>
            <leadership_teamwork>
            <score></score>
            <feedback></feedback>
            </leadership_teamwork>
            </skills_and_traits>

            <alignment_with_job>
            <skills_match>
            <score></score>
            <feedback></feedback>
            </skills_match>
            <job_title_match>
            <score></score>
            <feedback></feedback>
            </job_title_match>
            <responsibilities_qualifications>
            <score></score>
            <feedback></feedback>
            </responsibilities_qualifications>
            <industry_keywords_synonyms>
            <score></score>
            <feedback></feedback>
            </industry_keywords_synonyms>
            </alignment_with_job>

            <overall_recommendation>
            <overall_score></overall_score>
            <improvements>
            <strengths>
            </strengths>
            <areas_for_improvement>
            </areas_for_improvement>
            </improvements>
            </overall_recommendation>

            <specific_change>
            [JSON array of recommended immediate changes]
            </specific_change>
            </evaluation>

            Your evaluation must be fair, thorough, consistent, and focused on alignment with the job description.
        PROMPT;

        return str_replace(
            ['{{PROFILE_JSON}}', '{{JOB_JSON}}'],
            [$profileJson, $jobJson],
            $template
        );
    }

    /**
     * Parse the model output with tag-based extraction and safe fallbacks.
     *
     * @return array{0: array<string,mixed>, 1: string[]}
     */
    private function parseEvaluation(string $content): array
    {
        $errors = [];
        $get = function (string $s, string $tag): ?string {
            if (preg_match('/<'.preg_quote($tag, '/').'>\s*(.*?)\s*<\/'.preg_quote($tag, '/').'>/is', $s, $m)) {
                return trim($m[1]);
            }

            return null;
        };

        $eval = $get($content, 'evaluation');
        if ($eval === null) {
            $errors[] = 'Missing <evaluation> root tag.';
            $eval = $content; // try parsing top-level anyway
        }

        $parseSection = function (string $section, array $expectedKeys) use ($get, &$errors): array {
            $out = [];
            $block = $get($section, 'impact');
            if ($block === null) {
                $block = $get($section, 'skills_and_traits');
            }
            if ($block === null) {
                $block = $get($section, 'alignment_with_job');
            }
            if ($block === null) {
                $block = $section;
            }

            foreach ($expectedKeys as $key) {
                $chunk = $get($block, $key);
                if ($chunk !== null) {
                    $score = $get($chunk, 'score');
                    $feedback = $get($chunk, 'feedback');
                    $out[$key] = [
                        'score' => $this->toInt($score),
                        'feedback' => $feedback,
                    ];
                }
            }

            return $out;
        };

        $impactBlock = $get($eval, 'impact') ?? '';
        $skillsBlock = $get($eval, 'skills_and_traits') ?? '';
        $alignBlock = $get($eval, 'alignment_with_job') ?? '';

        $impact = $parseSection($impactBlock, [
            'quantifying_impact',
            'focus_on_achievements',
            'writing_quality',
            'varied_industry_specific_verbs',
        ]);

        $skills = $parseSection($skillsBlock, [
            'problem_solving',
            'communication_collaboration',
            'initiative_innovation',
            'leadership_teamwork',
        ]);

        $alignment = $parseSection($alignBlock, [
            'skills_match',
            'job_title_match',
            'responsibilities_qualifications',
            'industry_keywords_synonyms',
        ]);

        $overallSection = $get($eval, 'overall_recommendation') ?? '';
        $overallScore = $this->toInt($get($overallSection, 'overall_score'));
        
        // Extract the improvements section which contains strengths and areas for improvement
        $improvements = $get($overallSection, 'improvements');

        $changesRaw = $get($eval, 'specific_change');
        $specificChanges = [];
        if ($changesRaw) {
            $specificChanges = $this->decodeJsonLenient($changesRaw, $errors) ?? [];
        }

        $totalScore = $overallScore;
        if ($totalScore === null) {
            $sum = 0;
            $counted = 0;
            foreach ([$impact, $skills, $alignment] as $group) {
                foreach ($group as $item) {
                    if (isset($item['score']) && is_int($item['score'])) {
                        $sum += $item['score'];
                        $counted++;
                    }
                }
            }
            $totalScore = $sum > 0 ? $sum : null;
        }

        return [[
            'impact' => $impact,
            'skills_and_traits' => $skills,
            'alignment_with_job' => $alignment,
            'overall' => [
                'total_score' => $totalScore,
                'recommendation' => strip_tags($overallSection ?? ''),
                'improvements' => $improvements,
            ],
            'specific_changes' => $specificChanges,
        ], $errors];
    }

    private function toInt(?string $s): ?int
    {
        if ($s === null) {
            return null;
        }
        if (preg_match('/-?\d+/', $s, $m)) {
            return (int) $m[0];
        }

        return null;
    }

    private function decodeJsonLenient(string $json, array &$errors)
    {
        $s = trim($json);
        $decoded = json_decode($s, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        // Try to repair trailing commas
        $s = preg_replace('/,(\s*[}\]])/', '$1', $s) ?? $s;
        $decoded = json_decode($s, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        $errors[] = 'Failed to decode specific_changes JSON: '.json_last_error_msg();

        return null;
    }
}