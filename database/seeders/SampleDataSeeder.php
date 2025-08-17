<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Education;
use App\Models\Experience;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a sample user with related data
        $user = User::factory()->create([
            'name' => 'John Developer',
            'email' => 'john@example.com',
            'location' => 'San Francisco, CA',
            'website' => 'https://johndeveloper.com',
            'summary' => 'Full-stack developer with 5 years of experience in web development'
        ]);

        // Create sample education
        Education::factory()->create([
            'user_id' => $user->id,
            'school' => 'University of California',
            'degree' => 'Bachelor of Science',
            'field_of_study' => 'Computer Science',
            'start_date' => '2013-09-01',
            'end_date' => '2017-06-01',
        ]);

        // Create sample experience
        Experience::factory()->create([
            'user_id' => $user->id,
            'title' => 'Senior Software Engineer',
            'company' => 'Tech Corp',
            'start_date' => '2018-01-01',
            'end_date' => '2022-12-31',
            'description' => 'Worked on various web applications using PHP, JavaScript, and MySQL'
        ]);

        echo "Sample data created successfully!\n";
        echo "Users in database: " . User::count() . "\n";
    }
}
