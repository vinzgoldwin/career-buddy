<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\JobDescription;
use App\Models\ProfileEvaluation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileEvaluationStrengthsAndImprovementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_evaluation_extracts_strengths_and_areas_for_improvement()
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

        // Create a profile evaluation with sample improvements data
        $profileEvaluation = ProfileEvaluation::create([
            'user_id' => $user->id,
            'job_description_id' => $jobDescription->id,
            'total_score' => 85,
            'overall_recommendation' => 'Good candidate',
            'improvements' => '<strengths>
        • Proven full‑stack development experience with Node.js, TypeScript, and modern JavaScript frameworks.<br/>
        • Demonstrated leadership and initiative through AI‑driven product development and team coordination.<br/>
        • Solid exposure to AWS services (S3, EC2 training) and database technologies (MySQL, MongoDB).<br/>
      </strengths>
      <areas_for_improvement>
        • Add quantifiable results (e.g., “Reduced page load time by 35%”, “Handled 10,000 concurrent users”).<br/>
        • Incorporate missing required skills: Odoo integration, PostgreSQL, Redis, CI/CD pipelines, Docker, Terraform/CloudFormation, Lambda, CloudWatch, GraphQL, Kafka/RabbitMQ.<br/>
        • Re‑format experience descriptions into concise bullet points that start with strong action verbs.<br/>
        • Highlight architectural design, microservices, and infrastructure‑as‑code experience to align with the lead backend role.<br/>
        • Update the summary to explicitly mention backend leadership, Node.js/TypeScript expertise, and experience with AWS cloud architecture.
      </areas_for_improvement>',
            'strengths' => 'Proven full‑stack development experience with Node.js, TypeScript, and modern JavaScript frameworks.
Demonstrated leadership and initiative through AI‑driven product development and team coordination.
Solid exposure to AWS services (S3, EC2 training) and database technologies (MySQL, MongoDB).',
            'areas_for_improvement' => 'Add quantifiable results (e.g., "Reduced page load time by 35%", "Handled 10,000 concurrent users").
Incorporate missing required skills: Odoo integration, PostgreSQL, Redis, CI/CD pipelines, Docker, Terraform/CloudFormation, Lambda, CloudWatch, GraphQL, Kafka/RabbitMQ.
Re‑format experience descriptions into concise bullet points that start with strong action verbs.
Highlight architectural design, microservices, and infrastructure‑as‑code experience to align with the lead backend role.
Update the summary to explicitly mention backend leadership, Node.js/TypeScript expertise, and experience with AWS cloud architecture.',
        ]);

        // Test that the strengths and areas for improvement were saved correctly
        $this->assertNotNull($profileEvaluation->strengths);
        $this->assertNotNull($profileEvaluation->areas_for_improvement);
        
        // Test that the strengths contain expected content
        $this->assertStringContainsString('full‑stack development experience', $profileEvaluation->strengths);
        $this->assertStringContainsString('leadership and initiative', $profileEvaluation->strengths);
        
        // Test that the areas for improvement contain expected content
        $this->assertStringContainsString('quantifiable results', $profileEvaluation->areas_for_improvement);
        $this->assertStringContainsString('missing required skills', $profileEvaluation->areas_for_improvement);
    }
}