<?php

namespace Tests\Feature;

use App\Models\Education;
use App\Models\Experience;
use App\Models\LicenseAndCertification;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AiResumeBuilderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_prefills_existing_user_data_in_resume_builder()
    {
        // Create a user with existing data
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'location' => 'New York, NY',
            'website' => 'https://johndoe.com',
            'summary' => 'Experienced software developer'
        ]);

        // Create some related data with proper date formats
        Education::factory()->create([
            'user_id' => $user->id,
            'school' => 'University of California',
            'degree' => 'Bachelor of Science',
            'field_of_study' => 'Computer Science',
            'start_date' => '2013-09-01',
            'end_date' => '2017-06-01',
        ]);

        Experience::factory()->create([
            'user_id' => $user->id,
            'title' => 'Software Engineer',
            'company' => 'Tech Corp',
            'start_date' => '2018-01-01',
            'end_date' => '2022-12-31',
            'description' => 'Worked on various web applications'
        ]);

        LicenseAndCertification::factory()->create([
            'user_id' => $user->id,
            'name' => 'AWS Certified Developer',
            'issuing_organization' => 'Amazon Web Services',
            'issue_date' => '2020-05-01',
        ]);

        Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'E-commerce Platform',
            'description' => 'Built a full e-commerce solution',
            'start_date' => '2021-03-01',
            'end_date' => '2021-09-01',
        ]);

        Skill::factory()->create([
            'user_id' => $user->id,
            'name' => 'PHP',
            'proficiency_level' => 5,
        ]);

        // Act as the user and visit the resume builder page
        $response = $this->actingAs($user)->get(route('ai-resume-builder'));

        // Assert the response is successful
        $response->assertStatus(200);

        // Check that the prefill data is passed to the view
        $response->assertInertia(fn ($page) => $page
            ->component('ai/AiResumeBuilder')
            ->has('prefillData')
            ->where('prefillData.name', 'John Doe')
            ->where('prefillData.email', 'john@example.com')
            ->where('prefillData.location', 'New York, NY')
            ->where('prefillData.website', 'https://johndoe.com')
            ->where('prefillData.summary', 'Experienced software developer')
            ->has('prefillData.educations', 1)
            ->has('prefillData.experiences', 1)
            ->has('prefillData.licenses_and_certifications', 1)
            ->has('prefillData.projects', 1)
            ->has('prefillData.skills', 1)
        );
    }

    #[Test]
    public function it_shows_default_empty_arrays_when_user_has_no_data()
    {
        // Create a user with no existing data
        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com'
        ]);

        // Act as the user and visit the resume builder page
        $response = $this->actingAs($user)->get(route('ai-resume-builder'));

        // Assert the response is successful
        $response->assertStatus(200);

        // Check that default empty arrays are provided
        $response->assertInertia(fn ($page) => $page
            ->component('ai/AiResumeBuilder')
            ->has('prefillData')
            ->where('prefillData.name', 'Jane Doe')
            ->where('prefillData.email', 'jane@example.com')
            ->where('prefillData.location', null)
            ->where('prefillData.website', null)
            ->where('prefillData.summary', null)
            ->has('prefillData.educations', 1)
            ->has('prefillData.experiences', 1)
            ->has('prefillData.licenses_and_certifications', 1)
            ->has('prefillData.projects', 1)
            ->has('prefillData.skills', 1)
        );
    }

    #[Test]
    public function it_validates_mm_yyyy_date_format()
    {
        $user = User::factory()->create();

        // Try to submit with invalid date format
        $response = $this->actingAs($user)->post(route('ai-resume-builder.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'educations' => [
                [
                    'school' => 'University of California',
                    'start_date' => '2023-01-01', // Invalid format (should be MM/YYYY)
                ]
            ]
        ]);

        // Should fail validation
        $response->assertSessionHasErrors([
            'educations.0.start_date'
        ]);

        // Try with valid MM/YYYY format
        $response = $this->actingAs($user)->post(route('ai-resume-builder.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'educations' => [
                [
                    'school' => 'University of California',
                    'start_date' => '01/2023', // Valid format
                ]
            ]
        ]);

        // Should pass validation
        $response->assertSessionDoesntHaveErrors('educations.0.start_date');
    }
}
