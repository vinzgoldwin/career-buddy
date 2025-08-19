<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\ResumeStoreRequest;
use App\Models\EmploymentType;
use App\Services\PdfToTextService;
use App\Services\ResumeParsingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Log;

class AiResumeBuilderController extends Controller
{
    protected PdfToTextService $pdfToTextService;

    public function __construct(PdfToTextService $pdfToTextService)
    {
        $this->pdfToTextService = $pdfToTextService;
    }

    /**
     * Display the AI Resume Builder page.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();

        // Load user's related data
        $user->load(['education', 'experiences', 'licensesAndCertifications', 'projects', 'skills']);

        // Get all employment types, handle case where table doesn't exist
        try {
            $employmentTypes = EmploymentType::all();
        } catch (\Exception $e) {
            $employmentTypes = collect(); // Return empty collection if table doesn't exist
        }

        $aiParsedData = session('ai_parsed_data');

        return Inertia::render('ai/AiResumeBuilder', [
            'employmentTypes' => $employmentTypes,
            'aiParsedData' => $aiParsedData ?: null,
            'prefillData' => [
                'name' => $user->name,
                'location' => $user->location,
                'email' => $user->email,
                'website' => $user->website,
                'summary' => $user->summary,
                'educations' => $user->education->map(function ($education) {
                    return [
                        'school' => $education->school,
                        'degree' => $education->degree,
                        'field_of_study' => $education->field_of_study,
                        'start_date' => $this->convertDateToMonthYear($education->start_date ? $education->start_date->format('Y-m-d') : null),
                        'end_date' => $this->convertDateToMonthYear($education->end_date ? $education->end_date->format('Y-m-d') : null),
                        'currently_studying' => $education->currently_studying,
                        'grade' => $education->grade,
                        'activities' => $education->activities,
                    ];
                })->toArray() ?: [['school' => '', 'degree' => '', 'field_of_study' => '', 'start_date' => '', 'end_date' => '', 'currently_studying' => false, 'grade' => '', 'activities' => '']],

                'experiences' => $user->experiences->map(function ($experience) {
                    return [
                        'title' => $experience->title,
                        'company' => $experience->company,
                        'location' => $experience->location,
                        'start_date' => $this->convertDateToMonthYear($experience->start_date ? $experience->start_date->format('Y-m-d') : null),
                        'end_date' => $this->convertDateToMonthYear($experience->end_date ? $experience->end_date->format('Y-m-d') : null),
                        'currently_working' => $experience->currently_working,
                        'employment_type_id' => $experience->employment_type_id,
                        'industry' => $experience->industry,
                        'description' => $experience->description,
                    ];
                })->toArray() ?: [['title' => '', 'company' => '', 'location' => '', 'start_date' => '', 'end_date' => '', 'currently_working' => false, 'employment_type_id' => null, 'industry' => '', 'description' => '']],

                'licenses_and_certifications' => $user->licensesAndCertifications->map(function ($license) {
                    return [
                        'name' => $license->name,
                        'issuing_organization' => $license->issuing_organization,
                        'issue_date' => $this->convertDateToMonthYear($license->issue_date ? $license->issue_date->format('Y-m-d') : null),
                        'expiration_date' => $this->convertDateToMonthYear($license->expiration_date ? $license->expiration_date->format('Y-m-d') : null),
                        'credential_id' => $license->credential_id,
                        'credential_url' => $license->credential_url,
                    ];
                })->toArray() ?: [['name' => '', 'issuing_organization' => '', 'issue_date' => '', 'expiration_date' => '', 'credential_id' => '', 'credential_url' => '']],

                'projects' => $user->projects->map(function ($project) {
                    return [
                        'name' => $project->name,
                        'description' => $project->description,
                        'start_date' => $this->convertDateToMonthYear($project->start_date ? $project->start_date->format('Y-m-d') : null),
                        'end_date' => $this->convertDateToMonthYear($project->end_date ? $project->end_date->format('Y-m-d') : null),
                        'url' => $project->url,
                        'skills_used' => $project->skills_used,
                    ];
                })->toArray() ?: [['name' => '', 'description' => '', 'start_date' => '', 'end_date' => '', 'url' => '', 'skills_used' => '']],

                'skills' => $user->skills->map(function ($skill) {
                    return [
                        'name' => $skill->name,
                        'proficiency_level' => $skill->proficiency_level,
                    ];
                })->toArray() ?: [['name' => '', 'proficiency_level' => 3]],
            ],
        ]);
    }

    /**
     * Dedicated editor page (two-pane UI) with same data as index.
     */
    public function editor()
    {
        $user = Auth::user();

        // Load user's related data
        $user->load(['education', 'experiences', 'licensesAndCertifications', 'projects', 'skills']);

        // Get all employment types, handle case where table doesn't exist
        try {
            $employmentTypes = EmploymentType::all();
        } catch (\Exception $e) {
            $employmentTypes = collect();
        }

        $aiParsedData = session('ai_parsed_data');

        return Inertia::render('ai/ResumeEditor', [
            'employmentTypes' => $employmentTypes,
            'aiParsedData' => $aiParsedData ?: null,
            'prefillData' => [
                'name' => $user->name,
                'location' => $user->location,
                'email' => $user->email,
                'website' => $user->website,
                'summary' => $user->summary,
                'educations' => $user->education->map(function ($education) {
                    return [
                        'school' => $education->school,
                        'degree' => $education->degree,
                        'field_of_study' => $education->field_of_study,
                        'start_date' => $this->convertDateToMonthYear($education->start_date ? $education->start_date->format('Y-m-d') : null),
                        'end_date' => $this->convertDateToMonthYear($education->end_date ? $education->end_date->format('Y-m-d') : null),
                        'currently_studying' => $education->currently_studying,
                        'grade' => $education->grade,
                        'activities' => $education->activities,
                    ];
                })->toArray() ?: [['school' => '', 'degree' => '', 'field_of_study' => '', 'start_date' => '', 'end_date' => '', 'currently_studying' => false, 'grade' => '', 'activities' => '']],

                'experiences' => $user->experiences->map(function ($experience) {
                    return [
                        'title' => $experience->title,
                        'company' => $experience->company,
                        'location' => $experience->location,
                        'start_date' => $this->convertDateToMonthYear($experience->start_date ? $experience->start_date->format('Y-m-d') : null),
                        'end_date' => $this->convertDateToMonthYear($experience->end_date ? $experience->end_date->format('Y-m-d') : null),
                        'currently_working' => $experience->currently_working,
                        'employment_type_id' => $experience->employment_type_id,
                        'industry' => $experience->industry,
                        'description' => $experience->description,
                    ];
                })->toArray() ?: [['title' => '', 'company' => '', 'location' => '', 'start_date' => '', 'end_date' => '', 'currently_working' => false, 'employment_type_id' => null, 'industry' => '', 'description' => '']],

                'licenses_and_certifications' => $user->licensesAndCertifications->map(function ($license) {
                    return [
                        'name' => $license->name,
                        'issuing_organization' => $license->issuing_organization,
                        'issue_date' => $this->convertDateToMonthYear($license->issue_date ? $license->issue_date->format('Y-m-d') : null),
                        'expiration_date' => $this->convertDateToMonthYear($license->expiration_date ? $license->expiration_date->format('Y-m-d') : null),
                        'credential_id' => $license->credential_id,
                        'credential_url' => $license->credential_url,
                    ];
                })->toArray() ?: [['name' => '', 'issuing_organization' => '', 'issue_date' => '', 'expiration_date' => '', 'credential_id' => '', 'credential_url' => '']],

                'projects' => $user->projects->map(function ($project) {
                    return [
                        'name' => $project->name,
                        'description' => $project->description,
                        'start_date' => $this->convertDateToMonthYear($project->start_date ? $project->start_date->format('Y-m-d') : null),
                        'end_date' => $this->convertDateToMonthYear($project->end_date ? $project->end_date->format('Y-m-d') : null),
                        'url' => $project->url,
                        'skills_used' => $project->skills_used,
                    ];
                })->toArray() ?: [['name' => '', 'description' => '', 'start_date' => '', 'end_date' => '', 'url' => '', 'skills_used' => '']],

                'skills' => $user->skills->map(function ($skill) {
                    return [
                        'name' => $skill->name,
                        'proficiency_level' => $skill->proficiency_level,
                    ];
                })->toArray() ?: [['name' => '', 'proficiency_level' => 3]],
            ],
        ]);
    }

    /**
     * Store the resume data.
     *
     * @return RedirectResponse
     */
    public function store(ResumeStoreRequest $request)
    {
        $validated = $request->validated();

        $user = Auth::user();

        // Update user's basic information
        $user->update([
            'location' => $validated['location'] ?? null,
            'website' => $validated['website'] ?? null,
            'summary' => $validated['summary'] ?? null,
        ]);

        // Process educations
        if (isset($validated['educations'])) {
            foreach ($validated['educations'] as $education) {
                if (! empty(array_filter($education))) {
                    $user->education()->updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'school' => $education['school'] ?? null,
                            'degree' => $education['degree'] ?? null,
                        ],
                        [
                            'field_of_study' => $education['field_of_study'] ?? null,
                            'start_date' => $this->convertMonthYearToDate($education['start_date'] ?? null),
                            'end_date' => $this->convertMonthYearToDate($education['end_date'] ?? null),
                            'currently_studying' => $education['currently_studying'] ?? false,
                            'grade' => $education['grade'] ?? null,
                            'activities' => $education['activities'] ?? null,
                        ]
                    );
                }
            }
        }

        // Process experiences
        if (isset($validated['experiences'])) {
            foreach ($validated['experiences'] as $experience) {
                if (! empty(array_filter($experience))) {
                    $user->experiences()->updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'title' => $experience['title'] ?? null,
                            'company' => $experience['company'] ?? null,
                        ],
                        [
                            'location' => $experience['location'] ?? null,
                            'start_date' => $this->convertMonthYearToDate($experience['start_date'] ?? null),
                            'end_date' => $this->convertMonthYearToDate($experience['end_date'] ?? null),
                            'currently_working' => $experience['currently_working'] ?? false,
                            'employment_type_id' => $experience['employment_type_id'] ?? null,
                            'industry' => $experience['industry'] ?? null,
                            'description' => $experience['description'] ?? null,
                        ]
                    );
                }
            }
        }

        // Process licenses and certifications
        if (isset($validated['licenses_and_certifications'])) {
            foreach ($validated['licenses_and_certifications'] as $license) {
                if (! empty(array_filter($license))) {
                    $user->licensesAndCertifications()->updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'name' => $license['name'] ?? null,
                            'issuing_organization' => $license['issuing_organization'] ?? null,
                        ],
                        [
                            'issue_date' => $this->convertMonthYearToDate($license['issue_date'] ?? null),
                            'expiration_date' => $this->convertMonthYearToDate($license['expiration_date'] ?? null),
                            'credential_id' => $license['credential_id'] ?? null,
                            'credential_url' => $license['credential_url'] ?? null,
                        ]
                    );
                }
            }
        }

        // Process projects
        if (isset($validated['projects'])) {
            foreach ($validated['projects'] as $project) {
                if (! empty(array_filter($project))) {
                    $user->projects()->updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'name' => $project['name'] ?? null,
                        ],
                        [
                            'description' => $project['description'] ?? null,
                            'start_date' => $this->convertMonthYearToDate($project['start_date'] ?? null),
                            'end_date' => $this->convertMonthYearToDate($project['end_date'] ?? null),
                            'url' => $project['url'] ?? null,
                            'skills_used' => $project['skills_used'] ?? null,
                        ]
                    );
                }
            }
        }

        // Process skills
        if (isset($validated['skills'])) {
            foreach ($validated['skills'] as $skill) {
                if (! empty($skill['name'])) {
                    $user->skills()->updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'name' => $skill['name'],
                        ],
                        [
                            'proficiency_level' => $skill['proficiency_level'] ?? 3,
                        ]
                    );
                }
            }
        }

        return redirect()->back()->with('success', 'Resume data saved successfully!');
    }

    public function uploadResume(Request $request)
    {
        set_time_limit(150);

        $request->validate([
            'resume' => 'required|file|mimes:pdf|max:10240', // Max 10MB
        ]);

        try {
            $path = $request->file('resume')->getRealPath();
            $text = $this->pdfToTextService->extract($path);

            // Process the extracted text with our resume parsing service
            $resumeParsingService = new ResumeParsingService;
            $response = $resumeParsingService->extractResumeInformation($text);

            // Extract JSON from the response
            $content = $response['choices'][0]['message']['content'] ?? '';

            if (preg_match('/<structured_json>(.*?)<\/structured_json>/s', $content, $matches)) {
                $jsonString = $matches[1];
                $jsonString = trim($jsonString);
                Log::info('Extracted JSON String', ['jsonString' => $jsonString]);

                try {
                    $structuredData = json_decode($jsonString, true);
                    Log::info('Parsed Structured Data', ['structuredData' => $structuredData]);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        // Flash structured data to session and redirect to the main page
                        return redirect()
                            ->route('ai-resume-builder')
                            ->with('ai_parsed_data', $structuredData);
                    } else {
                        Log::error('JSON Parse Error', ['error' => json_last_error_msg()]);
                    }
                } catch (\Exception $e) {
                    Log::error('Exception during JSON parsing', ['exception' => $e->getMessage()]);
                }
            }

            return redirect()->back()->with('error', 'Failed to parse resume data. Please try again.');
        } catch (\Exception $e) {
            Log::error('Exception during resume upload processing', ['exception' => $e->getMessage()]);

            return redirect()->back()->with('error', 'An error occurred while processing your resume. Please try again.');
        }
    }

    /**
     * Convert date format to MM/YYYY format.
     */
    private function convertDateToMonthYear(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        // Convert YYYY-MM-DD to MM/YYYY
        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        if ($dateObj) {
            return $dateObj->format('m/Y');
        }

        return $date;
    }

    /**
     * Convert MM/YYYY format to date format.
     */
    private function convertMonthYearToDate(?string $monthYear): ?string
    {
        if (! $monthYear) {
            return null;
        }

        // Check if it matches MM/YYYY format
        if (preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $monthYear)) {
            // Convert MM/YYYY to YYYY-MM-01
            $parts = explode('/', $monthYear);

            return $parts[1].'-'.$parts[0].'-01';
        }

        return $monthYear;
    }
}
