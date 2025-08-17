<?php

namespace Tests\Feature\Ai;

use App\Models\EmploymentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmploymentTypesInAiResumeBuilderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_passes_employment_types_to_the_view()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create employment types using the seeder
        $this->seed('EmploymentTypeSeeder');
        
        // Acting as the user
        $this->actingAs($user);
        
        // Make a request to the AI Resume Builder page
        $response = $this->get(route('ai-resume-builder'));
        
        // Assert the response is successful
        $response->assertStatus(200);
        
        // Assert that employment types are passed to the view
        $response->assertInertia(fn ($page) => $page
            ->component('ai/AiResumeBuilder')
            ->has('employmentTypes', 7)
        );
    }
}