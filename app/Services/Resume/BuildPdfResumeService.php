<?php

namespace App\Services\Resume;

use App\Services\Profile\ProfileJsonService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BuildPdfResumeService
{
    public function __construct(
        protected ProfileJsonService $profileJsonService,
    ) {}

    public function downloadForCurrentUser()
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $profile = $this->profileJsonService->buildForUser($user);

        $normalized = $this->normalizeProfile($profile);

        $html = view('pdf.resume', $normalized)->render();

        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        // Use a bundled Unicode-safe font to avoid glyph substitution issues
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = 'Resume - '.Str::of((string) $user->name)->squish()->limit(80)->toString().'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function normalizeProfile(array $profile): array
    {
        // Ensure consistent, reverse-chronological ordering and normalized values
        $parseDate = function (?string $mmYyyy): ?\DateTimeImmutable {
            if (! $mmYyyy) {
                return null;
            }

            // Accept formats like m/Y or mm/YYYY
            $parts = explode('/', $mmYyyy);
            if (count($parts) === 2) {
                [$m, $y] = $parts;
                $m = str_pad(trim((string) $m), 2, '0', STR_PAD_LEFT);
                $y = trim((string) $y);
                $date = \DateTimeImmutable::createFromFormat('Y-m-d', $y.'-'.$m.'-01');

                return $date ?: null;
            }

            return null;
        };

        $sortByDatesDesc = function (array $items, string $startKey = 'start_date', string $endKey = 'end_date') use ($parseDate): array {
            return collect($items)
                ->map(function ($item) use ($parseDate, $startKey, $endKey) {
                    $item['_start'] = $parseDate($item[$startKey] ?? null);
                    $item['_end'] = $parseDate($item[$endKey] ?? null);

                    return $item;
                })
                ->sortByDesc(function ($item) {
                    // Sort by end date (null means present), then by start date
                    $end = $item['_end'];
                    $start = $item['_start'];
                    // Treat null end_date (Present) as the most recent
                    $endTimestamp = $end?->getTimestamp() ?? PHP_INT_MAX;
                    $startTimestamp = $start?->getTimestamp() ?? 0;

                    return [$endTimestamp, $startTimestamp];
                })
                ->map(function ($item) {
                    unset($item['_start'], $item['_end']);

                    return $item;
                })
                ->values()
                ->all();
        };

        $experiences = $sortByDatesDesc($profile['experiences'] ?? []);
        $projects = $sortByDatesDesc($profile['projects'] ?? []);
        $educations = $sortByDatesDesc($profile['educations'] ?? []);
        $licenses = $sortByDatesDesc($profile['licenses_and_certifications'] ?? [], 'issue_date', 'expiration_date');

        $skills = collect($profile['skills'] ?? [])
            ->map(function ($s) {
                if (is_array($s)) {
                    return trim((string) ($s['name'] ?? ''));
                }

                return trim((string) $s);
            })
            ->filter()
            ->unique(fn ($s) => mb_strtolower($s))
            ->values()
            ->all();

        return [
            'name' => $profile['name'] ?? '',
            'email' => $profile['email'] ?? '',
            'phone' => $profile['phone'] ?? '',
            'location' => $profile['location'] ?? '',
            'website' => $profile['website'] ?? '',
            'summary' => $profile['summary'] ?? '',
            'experiences' => $experiences,
            'projects' => $projects,
            'educations' => $educations,
            'licenses' => $licenses,
            'skills' => $skills,
        ];
    }
}
