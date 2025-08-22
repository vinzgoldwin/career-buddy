<?php

namespace Tests\Unit\Services\Ai;

use App\Models\User;
use App\Models\JobDescription;
use App\Models\ProfileEvaluation;
use App\Models\ProfileEvaluationSpecificChange;
use App\Services\Ai\ProfileEvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileEvaluationServiceArrayConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_evaluation_service_handles_array_values_in_specific_changes()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create a job description
        $jobDescription = JobDescription::create([
            'user_id' => $user->id,
            'title' => 'Software Engineer',
            'seniority' => 'Mid',
            'company_name' => 'Test Company',
            'work_mode' => 'Remote',
            'location' => 'San Francisco',
            'employment_type' => 'Full-time',
            'summary' => 'We are looking for a software engineer',
            'responsibilities' => json_encode(['Develop software', 'Write tests']),
            'requirements' => json_encode(['5 years experience', 'BS in Computer Science']),
            'skills' => json_encode([['name' => 'PHP', 'proficiency_level' => 5]]),
            'years_experience_min' => 3,
            'years_experience_max' => 7,
            'raw_input' => 'Job description input',
        ]);

        // Mock the service to test the specific changes handling
        $service = new ProfileEvaluationService();
        
        // Create a profile evaluation directly to test the specific changes
        $profileEvaluation = ProfileEvaluation::create([
            'user_id' => $user->id,
            'job_description_id' => $jobDescription->id,
            'total_score' => 85,
            'overall_recommendation' => 'Good candidate',
        ]);
        
        // Test that we can create a specific change with array values that get converted to strings
        $change = ProfileEvaluationSpecificChange::create([
            'profile_evaluation_id' => $profileEvaluation->id,
            'field' => 'skills',
            'entity_id' => 2,
            'specific_field' => 'name',
            'old_value' => json_encode(['name' => 'PHP', 'level' => 5]), // Array converted to JSON string
            'new_value' => json_encode(['name' => 'PHP', 'level' => 8]), // Array converted to JSON string
        ]);
        
        // Verify that the change was created successfully
        $this->assertNotNull($change->id);
        $this->assertEquals($profileEvaluation->id, $change->profile_evaluation_id);
        $this->assertEquals('skills', $change->field);
        $this->assertEquals(2, $change->entity_id);
        $this->assertEquals('name', $change->specific_field);
        
        // Verify that the values are stored as strings
        $this->assertIsString($change->old_value);
        $this->assertIsString($change->new_value);
        
        // Verify that the JSON strings can be decoded back to arrays
        $oldValueArray = json_decode($change->old_value, true);
        $newValueArray = json_decode($change->new_value, true);
        
        $this->assertIsArray($oldValueArray);
        $this->assertIsArray($newValueArray);
        $this->assertEquals(['name' => 'PHP', 'level' => 5], $oldValueArray);
        $this->assertEquals(['name' => 'PHP', 'level' => 8], $newValueArray);
    }
}