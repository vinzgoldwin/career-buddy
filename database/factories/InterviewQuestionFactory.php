<?php

namespace Database\Factories;

use App\Models\InterviewQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InterviewQuestion>
 */
class InterviewQuestionFactory extends Factory
{
    protected $model = InterviewQuestion::class;

    public function definition(): array
    {
        $categories = [
            'Behavioral', 'Leadership', 'Problem Solving', 'Communication', 'Career Goals',
            'Ownership', 'Company-Specific', 'Self-Introduction', 'Self-Reflection', 'Stakeholder Management',
            'Time Management', 'Conflict Resolution', 'Collaboration',
        ];

        $difficulties = ['Easy', 'Medium', 'Hard'];

        return [
            'title' => $this->faker->sentence(10),
            'category' => $this->faker->randomElement($categories),
            'difficulty' => $this->faker->randomElement($difficulties),
            'views_count' => $this->faker->numberBetween(10, 5000),
            'users_practiced_count' => $this->faker->numberBetween(0, 3000),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
