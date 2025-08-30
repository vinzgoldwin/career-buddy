<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Interview Evaluation</title>
    <style>
        @page { margin: 36pt; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 11pt; line-height: 1.45; }
        h1 { font-size: 20pt; margin: 0 0 8pt 0; }
        h2 { font-size: 14pt; margin: 16pt 0 6pt 0; }
        .muted { color: #6b7280; }
        .badge { display: inline-block; padding: 2pt 6pt; border-radius: 8pt; border: 1pt solid #d1d5db; color: #374151; font-size: 9pt; }
        .section { margin-bottom: 12pt; }
        .card { border: 1pt solid #e5e7eb; border-radius: 8pt; padding: 10pt 12pt; margin-bottom: 8pt; }
        .mb-2 { margin-bottom: 6pt; }
        .mb-3 { margin-bottom: 8pt; }
        .mb-4 { margin-bottom: 12pt; }
        ul { padding-left: 14pt; margin: 6pt 0; }
        li { margin: 0 0 4pt 0; }
        .small { font-size: 9pt; }
        .title { font-weight: 700; }
        .row { width: 100%; }
        table.scores { width: 100%; border-collapse: separate; border-spacing: 8pt 8pt; }
        table.scores td { vertical-align: top; }
    </style>
    <meta name="pdf:generator" content="dompdf" />
</head>
<body>
    <div class="section" style="margin-bottom: 10pt;">
        <h1 class="title">Interview Evaluation</h1>
        @if (!empty($question['title']))
            <div class="muted">{{ $question['title'] }}</div>
        @endif
        @if (!empty($question['category']))
            <div><span class="badge" style="margin-top: 4pt; display: inline-block;">{{ $question['category'] }}</span></div>
        @endif
    </div>

    <div class="section">
        <h2>Your Answer</h2>
        <div class="card">
            @if (!empty($answer))
                <div>{!! nl2br(e($answer)) !!}</div>
            @else
                <div class="muted small">No answer provided.</div>
            @endif
        </div>
    </div>

    <div class="section">
        <h2>Scores</h2>
        <table class="scores">
            <tr>
                <td style="width: 50%">
                    <div class="card">
                        <div class="small muted">Overall Performance</div>
                        <div style="font-size: 16pt; font-weight: 700;">{{ $overall_performance['score'] ?? '—' }}<span class="small muted"> / 10</span></div>
                    </div>
                </td>
                <td style="width: 50%">
                    <div class="card">
                        <div class="small muted">Structural Integrity</div>
                        <div style="font-size: 16pt; font-weight: 700;">{{ $structural_integrity['score'] ?? '—' }}<span class="small muted"> / 10</span></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 50%">
                    <div class="card">
                        <div class="small muted">Content Accuracy</div>
                        <div style="font-size: 16pt; font-weight: 700;">{{ $content_accuracy['score'] ?? '—' }}<span class="small muted"> / 10</span></div>
                    </div>
                </td>
                <td style="width: 50%">
                    <div class="card">
                        <div class="small muted">Fluency</div>
                        <div style="font-size: 16pt; font-weight: 700;">{{ $fluency_of_expression['score'] ?? '—' }}<span class="small muted"> / 10</span></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Detailed Justifications</h2>
        <div class="card">
            <div class="title mb-2">Overall Performance</div>
            <div class="muted">{!! nl2br(e($overall_performance['justification'] ?? '')) !!}</div>
        </div>
        <div class="card">
            <div class="title mb-2">Structural Integrity</div>
            <div class="muted">{!! nl2br(e($structural_integrity['justification'] ?? '')) !!}</div>
        </div>
        <div class="card">
            <div class="title mb-2">Content Accuracy</div>
            <div class="muted">{!! nl2br(e($content_accuracy['justification'] ?? '')) !!}</div>
        </div>
        <div class="card">
            <div class="title mb-2">Fluency of Expression</div>
            <div class="muted">{!! nl2br(e($fluency_of_expression['justification'] ?? '')) !!}</div>
        </div>
    </div>

    <div class="section">
        <h2>Strengths</h2>
        <div class="card">
            @if (!empty($strengths))
                <ul>
                    @foreach ($strengths as $s)
                        <li>{{ $s }}</li>
                    @endforeach
                </ul>
            @else
                <div class="muted small">No strengths provided.</div>
            @endif
        </div>
    </div>

    <div class="section">
        <h2>Priority Improvements</h2>
        <div class="card">
            @if (!empty($priority_areas_for_improvement))
                <ul>
                    @foreach ($priority_areas_for_improvement as $s)
                        <li>{{ $s }}</li>
                    @endforeach
                </ul>
            @else
                <div class="muted small">No improvements provided.</div>
            @endif
        </div>
    </div>

    <div class="section">
        <h2>Comparative Analysis</h2>
        <div class="card">
            @if (!empty($comparative_analysis))
                <ul>
                    @foreach ($comparative_analysis as $s)
                        <li>{{ $s }}</li>
                    @endforeach
                </ul>
            @else
                <div class="muted small">No comparison provided.</div>
            @endif
        </div>
    </div>

    <div class="section">
        <h2>Encouraging Advice</h2>
        <div class="card">
            @if (!empty($encouraging_advice))
                <ul>
                    @foreach ($encouraging_advice as $s)
                        <li>{{ $s }}</li>
                    @endforeach
                </ul>
            @else
                <div class="muted small">No advice provided.</div>
            @endif
        </div>
    </div>
</body>
</html>
