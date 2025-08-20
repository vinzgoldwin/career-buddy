<?php

namespace App\Services\Job;

class JobDescriptionParserService
{
    /**
     * Parse a raw job description string into a structured JSON-ready array.
     */
    public function parse(string $raw): array
    {
        $text = trim(str_replace("\r\n", "\n", $raw));

        $sections = $this->splitSections($text);

        $company = $this->extractCompany($text, $sections['about'] ?? '');
        $role = $this->extractRole($text, $sections['job_summary'] ?? '');
        $website = $this->extractWebsite($text);

        $responsibilities = $this->extractBullets($sections['responsibilities'] ?? '');
        $required = $this->extractBullets($sections['required_qualifications'] ?? '');
        $preferred = $this->extractBullets($sections['preferred_qualifications'] ?? '');

        $experience = $this->extractExperience($text);
        $technologies = $this->extractTechnologies($text);

        return [
            'company' => [
                'name' => $company['name'] ?? null,
                'website' => $website,
                'about' => $sections['about'] ?? null,
            ],
            'role' => [
                'title' => $role['title'] ?? null,
                'department' => null,
                'seniority' => $role['seniority'] ?? null,
            ],
            'summary' => $sections['job_summary'] ?? null,
            'responsibilities' => $responsibilities,
            'qualifications' => [
                'required' => $required,
                'preferred' => $preferred,
            ],
            'experience' => $experience,
            'technologies' => $technologies,
            'location' => $this->extractLocation($text),
            'employment_type' => $this->extractEmploymentType($text),
            'compensation' => null,
            'notes' => $this->extractNotes($text),
            'raw' => $text,
        ];
    }

    private function splitSections(string $text): array
    {
        // Normalize multiple newlines to single blank lines preserved
        $lines = array_map(fn ($l) => trim($l), explode("\n", $text));

        $current = 'preamble';
        $sections = [
            'about' => '',
            'job_summary' => '',
            'responsibilities' => '',
            'required_qualifications' => '',
            'preferred_qualifications' => '',
        ];

        $map = [
            'about the job' => 'job_summary',
            'about' => 'about',
            'job summary' => 'job_summary',
            'job description' => 'job_summary',
            'key responsibilities' => 'responsibilities',
            'responsibilities' => 'responsibilities',
            'required qualifications' => 'required_qualifications',
            'qualifications' => 'required_qualifications',
            'kualifikasi' => 'required_qualifications',
            'preferred qualifications' => 'preferred_qualifications',
            'nice to have' => 'preferred_qualifications',
        ];

        foreach ($lines as $line) {
            $lc = strtolower(trim($line, ": \\t"));
            if (isset($map[$lc])) {
                $current = $map[$lc];
                continue;
            }

            if ($current === 'preamble') {
                // Try to detect an About Company opener like "About X"
                if (preg_match('/^about\s+([a-z0-9\-&., ]{2,})$/i', $lc)) {
                    $current = 'about';
                }
            }

            if ($current === 'preamble') {
                // Accumulate into summary if clearly non-company intro
                $sections['job_summary'] .= ($sections['job_summary'] ? "\n" : '').$line;
            } else {
                $sections[$current] .= ($sections[$current] ? "\n" : '').$line;
            }
        }

        foreach ($sections as $k => $v) {
            $sections[$k] = trim($v) ?: null;
        }

        return $sections;
    }

    private function extractBullets(?string $section): array
    {
        if (! $section) {
            return [];
        }
        $out = [];
        $lines = explode("\n", $section);
        
        // Handle both bulleted and non-bulleted formats
        $isBulleted = false;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            
            // Check if line starts with a bullet marker
            if (preg_match('/^[\-*\x{2022}\x{25CF}\x{25E6}\x{2023}]\s*/u', $line)) {
                $isBulleted = true;
                // Remove common bullet markers
                $line = preg_replace('/^[\-*\x{2022}\x{25CF}\x{25E6}\x{2023}]\s*/u', '', $line) ?? $line;
                $out[] = rtrim($line, '.');
            } else {
                // For non-bulleted formats, process each line as a separate item
                // Remove numbering if present (e.g., "1. ", "2. ")
                $line = preg_replace('/^\d+\.\s*/', '', $line) ?? $line;
                $out[] = rtrim($line, '.');
            }
        }
        
        // If it's not bulleted, we might want to combine sentences differently
        // But for now, we'll treat each line as a separate item
        return $out;
    }

    private function extractCompany(string $text, ?string $about): array
    {
        $name = null;
        if (preg_match_all('/^about\s+([A-Z][A-Za-z0-9 &.,\-]{1,})$/mi', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $candidate = strtolower(trim($m[1]));
                if ($candidate !== 'the job') {
                    $name = trim($m[1]);
                    break;
                }
            }
        }

        if (! $name && $about) {
            // Fallback: first word before "is a" pattern
            if (preg_match('/^([A-Z][A-Za-z0-9 &.,\-]{1,})\s+is\s+a/i', $about, $m2)) {
                $name = trim($m2[1]);
            }
        }

        return [
            'name' => $name,
        ];
    }

    private function extractWebsite(string $text): ?string
    {
        if (preg_match('/https?:\/\/[\w\.-]+\.[a-z]{2,}(?:\/[\w\-\/.?=&%#]*)?/i', $text, $m)) {
            return $m[0];
        }

        // Also handle www. without scheme
        if (preg_match('/www\.[\w\.-]+\.[a-z]{2,}(?:\/[\w\-\/.?=&%#]*)?/i', $text, $m2)) {
            return 'https://'.$m2[0];
        }

        return null;
    }

    private function extractRole(string $text, ?string $summary): array
    {
        $title = null;
        $seniority = null;

        $hay = $summary ?: $text;

        // Common pattern: "We are seeking a <TITLE> to" or "We are seeking an <TITLE> to"
        if (preg_match('/we\s+are\s+seeking\s+an?\s+([^\.\n]+?)\s+to/i', $hay, $m)) {
            $title = trim($m[1]);
        }

        // Fallback: look for tokens ending with Developer/Engineer/etc.
        if (! $title && preg_match('/\b([A-Za-z0-9\.\-\/+ ]+\b(?:Developer|Engineer|Manager|Architect|Specialist))\b/i', $hay, $m2)) {
            $title = trim($m2[1]);
        }

        if ($title && preg_match('/\b(Senior|Junior|Lead|Principal|Staff)\b/i', $title, $ms)) {
            $seniority = ucfirst(strtolower($ms[1]));
        }

        return [
            'title' => $title,
            'seniority' => $seniority,
        ];
    }

    private function extractExperience(string $text): array
    {
        $all = [];
        // Match ranges with en-dash first
        if (preg_match_all('/(\d+)–(\d+)\s+(?:years?|tahun)/i', $text, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $all[] = (int) $match[1];
                $all[] = (int) $match[2];
            }
        }
        // Match ranges with regular dash
        if (preg_match_all('/(\d+)-(\d+)\s+(?:years?|tahun)/i', $text, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $all[] = (int) $match[1];
                $all[] = (int) $match[2];
            }
        }
        // Match single year specifications (but not those that are part of ranges)
        // Only match if there's no dash or en-dash before the number
        if (preg_match_all('/(?<![-–\d])\d+\+?\s+(?:years?|tahun)(?!\s*[-–])/', $text, $m)) {
            foreach ($m[0] as $match) {
                // Extract the number from the match
                if (preg_match('/(\d+)/', $match, $numMatch)) {
                    $all[] = (int) $numMatch[1];
                }
            }
        }
        $uniqueAll = array_unique($all);
        $totalMin = $uniqueAll ? min($uniqueAll) : null;
        $totalMax = $uniqueAll ? max($uniqueAll) : null;

        $domain = null;
        $domainYears = null;
        // Match both English and Indonesian formats for domain experience
        if (preg_match('/(\d+)\s*(?:years?|tahun)\s+of\s+experience\s+in\s+the\s+([a-z ]+?)\s+(sector|industry)/i', $text, $dm) || 
            preg_match('/(\d+)\s*(?:years?|tahun)\s+.*?(?:pengalaman|experience).*?(?:di|in)\s+([a-z ]+?)\s+(?:sector|industry|industri)/i', $text, $dm)) {
            $domainYears = (int) $dm[1];
            $domain = trim($dm[2]);
        }

        return [
            'total_years_min' => $totalMin,
            'total_years_max' => $totalMax,
            'domain_experience' => $domain ? [[
                'domain' => $domain,
                'years' => $domainYears,
            ]] : [],
        ];
    }

    private function extractTechnologies(string $text): array
    {
        $catalog = [
            'languages' => ['Node.js', 'Node', 'Python', 'Java', 'JavaScript', 'PHP', 'C#', 'C++', 'Go', 'Ruby', 'Swift', 'Kotlin'],
            'frameworks' => ['NestJS', 'Spring Boot', 'Laravel', 'Express', 'Django', 'Flask', 'React', 'Vue', 'Angular'],
            'apis' => ['REST', 'GraphQL', 'SOAP'],
            'architecture' => ['Microservices', 'Monolith', 'Event Driven', 'Serverless'],
            'databases' => ['PostgreSQL', 'MySQL', 'MongoDB', 'Oracle', 'SQL Server', 'Redis', 'Cassandra', 'Elasticsearch'],
            'cloud' => ['AWS', 'Azure', 'GCP', 'Google Cloud', 'Amazon Web Services', 'Microsoft Azure', 'DigitalOcean', 'Heroku'],
            'tools' => ['Docker', 'CI/CD', 'Git', 'Kubernetes', 'Jenkins', 'GitLab', 'GitHub Actions', 'Terraform'],
            'methodologies' => ['Agile', 'Scrum', 'DevOps', 'TDD', 'Test Driven Development'],
        ];

        $found = [];
        foreach ($catalog as $group => $items) {
            $found[$group] = [];
            foreach ($items as $token) {
                $pattern = '/\b'.preg_quote($token, '/').'\b/i';
                if (preg_match($pattern, $text)) {
                    $found[$group][] = $token;
                }
            }
        }

        return $found;
    }

    private function extractLocation(string $text): ?string
    {
        // Very light heuristic placeholder; can be extended later.
        if (preg_match('/\b(Remote|Hybrid|On-site)\b/i', $text, $m)) {
            return ucfirst(strtolower($m[1]));
        }

        return null;
    }

    private function extractEmploymentType(string $text): ?string
    {
        if (preg_match('/\b(Full[- ]?time|Part[- ]?time|Contract|Internship|Temporary)\b/i', $text, $m)) {
            return ucfirst(strtolower(str_replace('-', ' ', $m[1])));
        }

        return null;
    }

    private function extractNotes(string $text): array
    {
        $notes = [];
        if (preg_match('/equal\s+opportunit(y|ies)/i', $text)) {
            $notes[] = 'Equal opportunities statement present';
        }
        if (preg_match('/training|learning\s+programs?/i', $text)) {
            $notes[] = 'Training and development mentioned';
        }

        return $notes;
    }
}
