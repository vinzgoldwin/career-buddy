<?php

namespace Tests\Feature;

use App\Models\LicenseAndCertification;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AiResumeStoreTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_and_deletes_resume_sections(): void
    {
        $user = User::factory()->create();
        $skill = Skill::factory()->create(['user_id' => $user->id, 'name' => 'PHP']);
        $project = Project::factory()->create(['user_id' => $user->id, 'name' => 'Old Project']);
        $license = LicenseAndCertification::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old Cert',
            'issuing_organization' => 'Org',
            'issue_date' => '2020-01-01',
        ]);

        $response = $this->actingAs($user)->post(route('ai-resume-builder.store'), [
            'name' => $user->name,
            'email' => $user->email,
            'skills' => [],
            'projects' => [],
            'licenses_and_certifications' => [
                [
                    'id' => $license->id,
                    'name' => 'Updated Cert',
                    'issuing_organization' => 'Org',
                    'issue_date' => '01/2020',
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseCount('skills', 0);
        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseHas('license_and_certifications', [
            'id' => $license->id,
            'name' => 'Updated Cert',
            'issuing_organization' => 'Org',
        ]);
    }
}
