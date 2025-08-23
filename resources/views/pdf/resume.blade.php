<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resume</title>
    <style>
        /* ATS-friendly: single column, minimal styling, standard fonts */
        @page { margin: 36pt 45pt; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11pt; color: #111; }
        h1 { font-size: 18pt; margin: 0 0 2pt 0; font-weight: 700; }
        h2 { font-size: 12pt; margin: 16pt 0 6pt 0; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        h3 { font-size: 11pt; margin: 0; font-weight: 700; }
        p { margin: 0 0 6pt 0; line-height: 1.4; }
        .muted { color: #444; }
        .small { font-size: 10pt; }
        /* Allow sections to break across pages; keep individual items intact */
        .section { page-break-inside: auto; }
        .item { page-break-inside: avoid; }
        .meta { margin-top: 2pt; }
        ul { padding-left: 14pt; margin: 6pt 0; }
        li { margin: 0 0 4pt 0; page-break-inside: avoid; }
        .header { border-bottom: 1px solid #ccc; padding-bottom: 8pt; margin-bottom: 10pt; }
        .contact { font-size: 10pt; color: #333; }
        .spacer { height: 6pt; }
        .mono { font-family: monospace; }
    </style>
    <!-- No images, icons, or two-column layout to ensure ATS compatibility -->
    <!-- Consistent date format: MM/YYYY -->
    <!-- Reverse-chronological ordering handled in service -->
    <!-- Standard headings: Summary, Experience, Projects, Education, Certifications, Skills -->
    <!-- Minimal graphics: only simple lines and text -->
</head>
<body>
    <div class="header">
        <h1>{{ $name }}</h1>
        <div class="contact">
            @if($location) <span>{{ $location }}</span>@endif
            @if($email) <span> | {{ $email }}</span>@endif
            @if($phone) <span> | {{ $phone }}</span>@endif
            @if($website) <span> | {{ $website }}</span>@endif
        </div>
    </div>

    @if($summary)
        <div class="section">
            <h2>Professional Summary</h2>
            <p>{{ $summary }}</p>
        </div>
    @endif

    @if(!empty($experiences))
        <div class="section">
            <h2>Experience</h2>
            @foreach($experiences as $exp)
                <div class="item" style="margin-bottom: 8pt;">
                    <h3>{{ $exp['title'] ?? '' }}</h3>
                    <p class="muted small">
                        {{ $exp['company'] ?? '' }}
                        @if(!empty($exp['location'])) • {{ $exp['location'] }} @endif
                    </p>
                    <p class="muted small meta">
                        @php
                            $start = $exp['start_date'] ?? null;
                            $end = $exp['currently_working'] ?? false ? 'Present' : ($exp['end_date'] ?? null);
                        @endphp
                        @if($start)
                            <span>{{ $start }}</span>
                        @endif
                        @if($start || $end)
                            <span> - </span>
                        @endif
                        @if($end)
                            <span>{{ $end }}</span>
                        @endif
                    </p>
                    @if(!empty($exp['description']))
                        @php
                            $lines = preg_split("/(\r?\n)+/", (string) $exp['description']);
                        @endphp
                        <ul>
                            @foreach($lines as $line)
                                @if(trim($line) !== '')
                                    <li>{{ trim($line) }}</li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($projects))
        <div class="section">
            <h2>Projects</h2>
            @foreach($projects as $project)
                <div class="item" style="margin-bottom: 8pt;">
                    <h3>{{ $project['name'] ?? '' }}</h3>
                    <p class="muted small meta">
                        @php $start = $project['start_date'] ?? null; $end = $project['end_date'] ?? null; @endphp
                        @if($start)
                            <span>{{ $start }}</span>
                        @endif
                        @if($start || $end)
                            <span> - </span>
                        @endif
                        @if($end)
                            <span>{{ $end }}</span>
                        @endif
                        @if(!empty($project['url']))
                            <span> • {{ $project['url'] }}</span>
                        @endif
                    </p>
                    @if(!empty($project['description']))
                        <p>{{ $project['description'] }}</p>
                    @endif
                    @if(!empty($project['skills_used']))
                        @php
                            $projectSkills = is_array($project['skills_used']) ? $project['skills_used'] : (array) $project['skills_used'];
                            $projectSkillsList = collect($projectSkills)->filter()->implode(', ');
                        @endphp
                        @if(!empty($projectSkillsList))
                            <p class="small">Skills: {{ $projectSkillsList }}</p>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($educations))
        <div class="section">
            <h2>Education</h2>
            @foreach($educations as $edu)
                <div class="item" style="margin-bottom: 6pt;">
                    <h3>{{ $edu['school'] ?? '' }}</h3>
                    <p class="muted small">{{ $edu['degree'] ?? '' }}@if(!empty($edu['field_of_study'])) in {{ $edu['field_of_study'] }}@endif</p>
                    <p class="muted small meta">
                        @php $start = $edu['start_date'] ?? null; $end = ($edu['currently_studying'] ?? false) ? 'Present' : ($edu['end_date'] ?? null); @endphp
                        @if($start)
                            <span>{{ $start }}</span>
                        @endif
                        @if($start || $end)
                            <span> - </span>
                        @endif
                        @if($end)
                            <span>{{ $end }}</span>
                        @endif
                    </p>
                    @if(!empty($edu['activities']))
                        <p>{{ $edu['activities'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($licenses))
        <div class="section">
            <h2>Certifications</h2>
            @foreach($licenses as $lic)
                <div class="item" style="margin-bottom: 6pt;">
                    <h3>{{ $lic['name'] ?? '' }}</h3>
                    <p class="muted small">
                        {{ $lic['issuing_organization'] ?? '' }}
                        @php $issued = $lic['issue_date'] ?? null; $exp = $lic['expiration_date'] ?? null; @endphp
                        @if($issued)
                            Issued {{ $issued }}
                        @endif
                        @if($issued && $exp)
                            |
                        @endif
                        @if($exp)
                            Expires {{ $exp }}
                        @endif
                    </p>
                    @if(!empty($lic['credential_id']))
                        <p class="muted small">ID: {{ $lic['credential_id'] }}</p>
                    @endif
                    @if(!empty($lic['credential_url']))
                        <p class="muted small">URL: {{ $lic['credential_url'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($skills))
        <div class="section">
            <h2>Skills</h2>
            <p>{{ implode(', ', $skills) }}</p>
        </div>
    @endif
</body>
</html>
