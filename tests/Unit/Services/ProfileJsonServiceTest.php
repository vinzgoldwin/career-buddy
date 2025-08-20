<?php

namespace Tests\Unit\Services;

use App\Models\Education;
use App\Models\Experience;
use App\Models\LicenseAndCertification;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use App\Services\Profile\ProfileJsonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileJsonServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_builds_a_complete_profile_json_for_a_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123-456-7890',
            'location' => 'Remote',
            'website' => 'https://janedoe.dev',
            'summary' => 'Seasoned developer',
        ]);

        Experience::factory()->create([
            'user_id' => $user->id,
            'title' => 'Senior Engineer',
            'company' => 'Acme Inc',
            'location' => 'NYC',
            'description' => 'Worked on X',
            'start_date' => '2020-01-01',
            'end_date' => '2021-12-01',
            'currently_working' => false,
            'employment_type_id' => null,
            'industry' => 'Software',
        ]);

        Education::factory()->create([
            'user_id' => $user->id,
            'school' => 'Uni',
            'degree' => 'BS',
            'field_of_study' => 'CS',
            'description' => 'CS program',
            'start_date' => '2016-09-01',
            'end_date' => '2020-06-01',
            'currently_studying' => false,
            'grade' => 'A',
            'activities' => 'Coding club',
        ]);

        LicenseAndCertification::factory()->create([
            'user_id' => $user->id,
            'name' => 'AWS Cert',
            'issuing_organization' => 'Amazon',
            'description' => 'Cloud cert',
            'issue_date' => '2022-01-01',
            'expiration_date' => '2025-01-01',
            'credential_id' => 'ABC123',
            'credential_url' => 'https://example.com/cert',
        ]);

        Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Portfolio',
            'description' => 'Personal site',
            'start_date' => '2023-01-01',
            'end_date' => '2023-06-01',
            'url' => 'https://janedoe.dev',
            'skills_used' => ['Vue', 'Laravel'],
        ]);

        Skill::factory()->create([
            'user_id' => $user->id,
            'name' => 'Laravel',
            'proficiency_level' => 5,
        ]);

        $service = new ProfileJsonService();
        $profile = $service->buildForUser($user);

        $this->assertArrayHasKey('id', $profile);
        $this->assertArrayHasKey('name', $profile);
        $this->assertArrayHasKey('email', $profile);
        $this->assertArrayHasKey('phone', $profile);
        $this->assertArrayHasKey('location', $profile);
        $this->assertArrayHasKey('website', $profile);
        $this->assertArrayHasKey('summary', $profile);
        $this->assertArrayHasKey('educations', $profile);
        $this->assertArrayHasKey('experiences', $profile);
        $this->assertArrayHasKey('licenses_and_certifications', $profile);
        $this->assertArrayHasKey('projects', $profile);
        $this->assertArrayHasKey('skills', $profile);

        $this->assertSame('Jane Doe', $profile['name']);
        $this->assertCount(1, $profile['educations']);
        $this->assertCount(1, $profile['experiences']);
        $this->assertCount(1, $profile['licenses_and_certifications']);
        $this->assertCount(1, $profile['projects']);
        $this->assertCount(1, $profile['skills']);

        // Verify date formatting to MM/YYYY
        $this->assertSame('09/2016', $profile['educations'][0]['start_date']);
        $this->assertSame('06/2020', $profile['educations'][0]['end_date']);
        $this->assertSame('01/2020', $profile['experiences'][0]['start_date']);
        $this->assertSame('12/2021', $profile['experiences'][0]['end_date']);
        $this->assertSame('01/2022', $profile['licenses_and_certifications'][0]['issue_date']);
        $this->assertSame('01/2025', $profile['licenses_and_certifications'][0]['expiration_date']);
        $this->assertSame('01/2023', $profile['projects'][0]['start_date']);
        $this->assertSame('06/2023', $profile['projects'][0]['end_date']);
    }
}

