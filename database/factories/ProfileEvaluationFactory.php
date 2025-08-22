<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\JobDescription;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileEvaluationFactory extends Factory
{
    protected $model = \App\Models\ProfileEvaluation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'job_description_id' => JobDescription::factory(),
            'total_score' => rand(50, 100),
            'overall_recommendation' => $this->faker->paragraph(),
            'improvements' => $this->faker->paragraph(),
            'raw_output' => $this->faker->text(),
            'errors' => json_encode([]),
            'usage' => json_encode(['total_tokens' => rand(1000, 5000)]),
            'llm_model' => 'openai/gpt-4',
        ];
    }
}