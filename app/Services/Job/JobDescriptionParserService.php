<?php

namespace App\Services\Job;

use App\Models\JobDescription;
use Illuminate\Support\Arr;
use OpenAI\Laravel\Facades\OpenAI;

class JobDescriptionParserService
{
    /**
     * Lean LLM-driven parser with strict JSON shape and post-parse normalization.
     */
    public function parse(string $raw): array
    {
        $prompt = $this->createJobParsingPrompt($raw);
        $model = config('ai.models.job_parsing', 'openai/gpt-oss-20b');
        $maxTokens = (int) config('ai.tokens.job_parsing', 5000);

        $resp = OpenAI::chat()->create([
            'model' => $model,
            'temperature' => 0,
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ])->toArray();

        $rawContent = $resp['choices'][0]['message']['content'] ?? '';

        [$json, $errors] = $this->extractStructuredJson($rawContent);

        if ($json === null) {
            return [
                'data' => $this->defaultStructure(),
                'errors' => array_merge(['Failed to decode model JSON.'], $errors),
                'raw' => $rawContent,
                'usage' => $resp['usage'] ?? null,
                'model' => $resp['model'] ?? $model,
            ];
        }

        $normalized = $this->normalize($json);

        return [
            'data' => $normalized,
            'errors' => $errors,
            'raw' => $rawContent,
            'usage' => $resp['usage'] ?? null,
            'model' => $resp['model'] ?? $model,
        ];
    }

    /**
     * Parse with LLM and persist result for later reuse.
     * Returns [model: JobDescription, data: array, errors: array, raw: string, usage: array|null, llm_model: string|null]
     */
    public function parseAndStore(string $raw, ?int $userId = null): array
    {
        $result = $this->parse($raw);
        $model = $this->storeNormalized(
            $result['data'] ?? [],
            $raw,
            $userId,
            [
                'llm_output_raw' => $result['raw'] ?? null,
                'errors' => $result['errors'] ?? [],
                'usage' => $result['usage'] ?? null,
                'llm_model' => $result['model'] ?? null,
            ]
        );

        return [
            'model' => $model,
            'data' => $result['data'] ?? [],
            'errors' => $result['errors'] ?? [],
            'raw' => $result['raw'] ?? null,
            'usage' => $result['usage'] ?? null,
            'llm_model' => $result['model'] ?? null,
        ];
    }

    /**
     * Persist a normalized job description without calling the LLM.
     * Useful for tests and for saving edited job postings.
     */
    public function storeNormalized(array $normalized, string $rawInput, ?int $userId = null, array $meta = []): JobDescription
    {
        return JobDescription::create([
            'user_id' => $userId,
            'title' => Arr::get($normalized, 'title'),
            'seniority' => Arr::get($normalized, 'seniority'),
            'company_name' => Arr::get($normalized, 'company_name'),
            'work_mode' => Arr::get($normalized, 'work_mode'),
            'location' => Arr::get($normalized, 'location'),
            'employment_type' => Arr::get($normalized, 'employment_type'),
            'summary' => Arr::get($normalized, 'summary'),
            'responsibilities' => Arr::get($normalized, 'responsibilities', []),
            'requirements' => Arr::get($normalized, 'requirements', []),
            'skills' => Arr::get($normalized, 'skills', []),
            'years_experience_min' => Arr::get($normalized, 'years_experience_min'),
            'years_experience_max' => Arr::get($normalized, 'years_experience_max'),
            'raw_input' => $rawInput,
            'llm_output_raw' => $meta['llm_output_raw'] ?? null,
            'errors' => $meta['errors'] ?? null,
            'usage' => $meta['usage'] ?? null,
            'llm_model' => $meta['llm_model'] ?? null,
        ]);
    }

    /* =========================
     * PROMPT (LEAN)
     * ========================= */

    private function createJobParsingPrompt(string $jobText): string
    {
        // Minified schema to reduce tokens
        $schema = json_encode($this->defaultStructure(), JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
            You are an extraction engine. Convert the job posting below into a STRICT JSON object that matches the schema EXACTLY.
            All keys must exist. Use "" for missing strings, [] for arrays, and null for unknown numbers.
            Output ONLY JSON wrapped in <structured_json> tags. Output MUST be MINIFIED (one line, no spaces or newlines).

            Language:
            - If the posting is primarily Indonesian, write strings in Indonesian; otherwise English.

            Allowed enums (use null if not explicitly present):
            - seniority: ["Entry","Junior","Mid","Senior","Lead","Principal","Staff","Head","Director","VP"]
            - work_mode: ["Remote","Hybrid","Onsite"]
            - employment_type: ["Full time","Part time","Contract","Fixed term","Permanent","Internship","Temporary","Freelance","Project based","B2B","Apprentice","Graduate"]

            Hard rules (no guessing):
            - Do NOT infer seniority from years; only set if explicitly stated in the text. Else null.
            - Do NOT infer employment_type unless explicitly stated. Else null.
            - work_mode only if clearly present ("Remote","Hybrid","On-site/Onsite","WFH","WFO"). Map: "WFH" => "Remote" if fully remote; "WFH most of the time with some in-person" => "Hybrid"; "WFO/On-site" => "Onsite". If unclear, null.
            - location: set only if explicitly stated (city/region/country). If absent, null.
            - company_name: prefer the actual employer; if a recruiter post says "on behalf of <X>", use X. If no employer is clear, use the posting entity name or leave "".

            Content shaping:
            - summary: 1 short neutral sentence, plain text, <= 280 chars, no emojis/symbols.
            - responsibilities: up to 12 concise bullets, each a single clause. Ignore culture/perks.
            - requirements: up to 12 concise bullets from qualifications/requirements.
            - skills: up to 20 unique TECH tokens (1–3 words each). No sentences. No soft skills. Case-sensitive tech names.
              Canonicalize common variants: "Node.js","React.js","Next.js","Vue.js",".NET","CI/CD","AWS","GCP","Azure","Elasticsearch","Datadog","TDD","Unit testing".
              Deduplicate case-insensitively.
            - years_experience_min/max: integers or null. If only "5+ years" => min=5, max=null. If a range "3–5 years" => min=3, max=5.

            Formatting constraints:
            - Plain ASCII quotes (").
            - Valid JSON only. No trailing commas. No markdown. No extra keys or fields.
            - Keep the output MINIFIED (no spaces/newlines).

            Schema:
            $schema

            Job posting (plain text; may include noise/HTML):
            <job_text>
            {$jobText}
            </job_text>

            Return only:
            <structured_json>{...}</structured_json>
        PROMPT;
    }

    /* =========================
     * EXTRACTION & REPAIR
     * ========================= */

    /**
     * @return array{0: array<string,mixed>|null, 1: string[]}
     */
    private function extractStructuredJson(string $content): array
    {
        $errors = [];

        // Prefer tagged JSON
        if (preg_match('/<structured_json>(.*?)<\/structured_json>/is', $content, $m)) {
            $candidate = trim($m[1]);
            $decoded = $this->jsonDecodeSafe($candidate, $errors);
            if (is_array($decoded)) {
                return [$decoded, $errors];
            }

            $errors[] = 'Tagged JSON failed; attempting repair.';
            $candidate = $this->attemptRepair($candidate);
            $decoded = $this->jsonDecodeSafe($candidate, $errors);
            if (is_array($decoded)) {
                return [$decoded, $errors];
            }
        } else {
            $errors[] = 'Missing <structured_json> tags.';
        }

        // Fallback: first JSON object in content
        if (preg_match('/\{.*\}/s', $content, $m2)) {
            $candidate = trim($m2[0]);
            $decoded = $this->jsonDecodeSafe($candidate, $errors);
            if (is_array($decoded)) {
                return [$decoded, $errors];
            }

            $errors[] = 'Untagged JSON failed; attempting repair.';
            $candidate = $this->attemptRepair($candidate);
            $decoded = $this->jsonDecodeSafe($candidate, $errors);
            if (is_array($decoded)) {
                return [$decoded, $errors];
            }
        }

        return [null, $errors];
    }

    private function jsonDecodeSafe(string $json, array &$errors)
    {
        $json = $this->normalizeQuotes($json);
        $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $json) ?? $json;
        $decoded = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errors[] = 'json_decode error: '.json_last_error_msg();

            return null;
        }
        if (! is_array($decoded)) {
            $errors[] = 'Decoded JSON is not an object.';

            return null;
        }

        return $decoded;
    }

    private function attemptRepair(string $json): string
    {
        $s = $json;
        // Remove trailing commas
        $s = preg_replace('/,(\s*[}\]])/', '$1', $s) ?? $s;
        // Replace single quotes if object looks JSON-ish with single quotes
        if (preg_match('/[{\[]\s*\'/', $s)) {
            $s = preg_replace('/\'/', '"', $s) ?? $s;
        }
        // Strip NBSP and trailing whitespace
        $s = str_replace("\xC2\xA0", ' ', $s);
        $s = preg_replace('/\s+$/', '', $s) ?? $s;

        return $s;
    }

    private function normalizeQuotes(string $s): string
    {
        $map = [
            "\xE2\x80\x9C" => '"', // “
            "\xE2\x80\x9D" => '"', // ”
            "\xE2\x80\x98" => "'", // ‘
            "\xE2\x80\x99" => "'", // ’
        ];

        return strtr($s, $map);
    }

    /* =========================
     * NORMALIZATION (LEAN)
     * ========================= */

    private function normalize(array $data): array
    {
        $out = $this->overlay($this->defaultStructure(), $data);

        // Canonicalize enums
        $out['seniority'] = $this->canonSeniority($out['seniority']);
        $out['work_mode'] = $this->canonWorkMode($out['work_mode']);
        $out['employment_type'] = $this->canonEmploymentType($out['employment_type']);

        // Coerce primitives
        $out['title'] = $this->toString($out['title']);
        $out['company_name'] = $this->toString($out['company_name']);
        $out['location'] = $this->nullableString($out['location']);
        $out['summary'] = $this->toString($out['summary']);

        // Arrays of strings
        $out['responsibilities'] = $this->stringArray($out['responsibilities']);
        $out['requirements'] = $this->stringArray($out['requirements']);
        $out['skills'] = $this->stringArrayDedupCaseInsensitive($out['skills']);

        // Years
        $out['years_experience_min'] = $this->nullableInt($out['years_experience_min']);
        $out['years_experience_max'] = $this->nullableInt($out['years_experience_max']);

        // Sanity: if min > max, swap
        if ($out['years_experience_min'] !== null && $out['years_experience_max'] !== null) {
            if ($out['years_experience_min'] > $out['years_experience_max']) {
                [$out['years_experience_min'], $out['years_experience_max']] =
                    [$out['years_experience_max'], $out['years_experience_min']];
            }
        }

        return $out;
    }

    private function overlay(array $base, array $incoming): array
    {
        foreach ($base as $k => $_) {
            if (array_key_exists($k, $incoming)) {
                $base[$k] = $incoming[$k];
            }
        }

        return $base;
    }

    private function canonSeniority($v): ?string
    {
        if (! is_string($v) || $v === '') {
            return null;
        }
        $k = strtolower(preg_replace('/[\s\-\.]/', '', $v));
        $map = [
            'entry' => 'Entry', 'entrylevel' => 'Entry',
            'junior' => 'Junior', 'jr' => 'Junior', 'jr' => 'Junior',
            'mid' => 'Mid', 'midlevel' => 'Mid',
            'senior' => 'Senior', 'sr' => 'Senior', 'sr' => 'Senior',
            'lead' => 'Lead', 'principal' => 'Principal', 'staff' => 'Staff',
            'head' => 'Head', 'director' => 'Director', 'vp' => 'VP', 'vicepresident' => 'VP',
        ];

        return $map[$k] ?? null;
    }

    private function canonWorkMode($v): ?string
    {
        if (! is_string($v) || $v === '') {
            return null;
        }
        $k = strtolower(str_replace(['-', ' '], '', $v));
        if (in_array($k, ['remote'])) {
            return 'Remote';
        }
        if (in_array($k, ['hybrid'])) {
            return 'Hybrid';
        }
        if (in_array($k, ['onsite', 'onsite', 'on-site', 'wfo'])) {
            return 'Onsite';
        }

        return null;
    }

    private function canonEmploymentType($v): ?string
    {
        if (! is_string($v) || $v === '') {
            return null;
        }
        $k = strtolower(str_replace(['-', ' '], '', $v));
        $map = [
            'fulltime' => 'Full time', 'fulltime.' => 'Full time', 'fulltime,' => 'Full time',
            'parttime' => 'Part time',
            'contract' => 'Contract', 'contractor' => 'Contract',
            'fixedterm' => 'Fixed term',
            'permanent' => 'Permanent',
            'internship' => 'Internship', 'intern' => 'Internship',
            'temporary' => 'Temporary',
            'freelance' => 'Freelance',
            'projectbased' => 'Project based',
            'b2b' => 'B2B',
            'apprentice' => 'Apprentice', 'apprenticeship' => 'Apprentice',
            'graduate' => 'Graduate', 'graduateprogram' => 'Graduate',
        ];

        return $map[$k] ?? null;
    }

    private function toString($v): string
    {
        if (is_string($v)) {
            return trim($v);
        }
        if (is_array($v)) {
            return trim(implode(' ', array_map([$this, 'toString'], $v)));
        }
        if ($v === null) {
            return '';
        }

        return trim((string) $v);
    }

    private function nullableString($v): ?string
    {
        $s = $this->toString($v);

        return $s === '' ? null : $s;
    }

    private function nullableInt($v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_numeric($v)) {
            return (int) $v;
        }
        // Loose parse like "3 years"
        if (preg_match('/\d+/', (string) $v, $m)) {
            return (int) $m[0];
        }

        return null;
    }

    private function stringArray($v): array
    {
        if (! is_array($v)) {
            $v = ($v === null || $v === '') ? [] : [(string) $v];
        }
        $v = array_map(fn ($x) => $this->toString($x), $v);
        $v = array_values(array_filter($v, fn ($x) => $x !== ''));

        return $v;
    }

    private function stringArrayDedupCaseInsensitive($v): array
    {
        $arr = $this->stringArray($v);
        $seen = [];
        $out = [];
        foreach ($arr as $s) {
            $k = mb_strtolower($s);
            if (isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            $out[] = $s;
        }

        return $out;
    }

    /* =========================
     * LEAN DEFAULT SCHEMA
     * ========================= */

    private function defaultStructure(): array
    {
        return [
            'title' => '',
            'seniority' => null,
            'company_name' => '',
            'work_mode' => null,    // Remote | Hybrid | Onsite | null
            'location' => null,
            'employment_type' => null,    // canonicalized set or null
            'summary' => '',
            'responsibilities' => [],
            'requirements' => [],
            'skills' => [],
            'years_experience_min' => null,
            'years_experience_max' => null,
        ];
    }
}
