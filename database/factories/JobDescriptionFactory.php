<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\JobDescription;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobDescriptionFactory extends Factory
{
    protected $model = JobDescription::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->jobTitle(),
            'seniority' => $this->faker->randomElement(['Entry', 'Mid', 'Senior', 'Lead']),
            'company_name' => $this->faker->company(),
            'work_mode' => $this->faker->randomElement(['Remote', 'Hybrid', 'On-site']),
            'location' => $this->faker->city(),
            'employment_type' => $this->faker->randomElement(['Full-time', 'Part-time', 'Contract']),
            'summary' => $this->faker->paragraph(),
            'responsibilities' => json_encode([$this->faker->sentence(), $this->faker->sentence()]),
            'requirements' => json_encode([$this->faker->sentence(), $this->faker->sentence()]),
            'skills' => json_encode([['name' => $this->faker->word(), 'proficiency_level' => rand(1, 5)]]),
            'years_experience_min' => rand(1, 5),
            'years_experience_max' => rand(5, 10),
            'raw_input' => $this->faker->text(),
        ];
    }
}