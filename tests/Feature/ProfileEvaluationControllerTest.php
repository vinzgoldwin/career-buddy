<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\JobDescription;
use App\Models\ProfileEvaluation;
use App\Models\ProfileEvaluationImpact;
use App\Models\ProfileEvaluationSkillsAndTraits;
use App\Models\ProfileEvaluationAlignmentWithJob;
use App\Models\ProfileEvaluationSpecificChange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileEvaluationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_evaluation_controller_returns_correct_data_structure()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Authenticate the user
        $this->actingAs($user);
        
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

        // Create a profile evaluation
        $profileEvaluation = ProfileEvaluation::create([
            'user_id' => $user->id,
            'job_description_id' => $jobDescription->id,
            'total_score' => 85,
            'overall_recommendation' => 'Good candidate',
            'improvements' => '<strengths>Strong skills</strengths><areas_for_improvement>Need more experience</areas_for_improvement>',
            'strengths' => 'Strong skills',
            'areas_for_improvement' => 'Need more experience',
        ]);

        // Create related records
        $impact = ProfileEvaluationImpact::create([
            'profile_evaluation_id' => $profileEvaluation->id,
            'quantifying_impact_score' => 8,
            'quantifying_impact_feedback' => 'Good quantifying of impact',
            'focus_on_achievements_score' => 9,
            'focus_on_achievements_feedback' => 'Great focus on achievements',
            'writing_quality_score' => 7,
            'writing_quality_feedback' => 'Good writing quality',
            'varied_industry_specific_verbs_score' => 8,
            'varied_industry_specific_verbs_feedback' => 'Good variety of verbs',
        ]);

        $skills = ProfileEvaluationSkillsAndTraits::create([
            'profile_evaluation_id' => $profileEvaluation->id,
            'problem_solving_score' => 9,
            'problem_solving_feedback' => 'Excellent problem solving skills',
            'communication_collaboration_score' => 8,
            'communication_collaboration_feedback' => 'Good communication skills',
            'initiative_innovation_score' => 7,
            'initiative_innovation_feedback' => 'Shows initiative',
            'leadership_teamwork_score' => 9,
            'leadership_teamwork_feedback' => 'Strong leadership skills',
        ]);

        $alignment = ProfileEvaluationAlignmentWithJob::create([
            'profile_evaluation_id' => $profileEvaluation->id,
            'skills_match_score' => 8,
            'skills_match_feedback' => 'Good skills match',
            'job_title_match_score' => 9,
            'job_title_match_feedback' => 'Excellent job title match',
            'responsibilities_qualifications_score' => 7,
            'responsibilities_qualifications_feedback' => 'Good responsibilities match',
            'industry_keywords_synonyms_score' => 8,
            'industry_keywords_synonyms_feedback' => 'Good use of industry keywords',
        ]);

        $change = ProfileEvaluationSpecificChange::create([
            'profile_evaluation_id' => $profileEvaluation->id,
            'field' => 'summary',
            'entity_id' => 1,
            'specific_field' => 'summary',
            'old_value' => 'Old summary',
            'new_value' => 'New summary',
        ]);

        // Make a request to the controller
        $response = $this->get(route('ai-evaluation.show', $profileEvaluation));
        
        // Assert that the response is successful
        $response->assertStatus(200);
        
        // Check that the response contains the expected data
        $response->assertSee('Good candidate');
        $response->assertSee('Strong skills');
        $response->assertSee('Need more experience');
    }
}