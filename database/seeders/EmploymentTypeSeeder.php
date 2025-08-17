<?php

namespace Database\Seeders;

use App\Models\EmploymentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmploymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employmentTypes = [
            'Full-time',
            'Part-time',
            'Contract',
            'Internship',
            'Voluntary',
            'Freelance',
            'Self-employed',
        ];

        foreach ($employmentTypes as $employmentType) {
            EmploymentType::firstOrCreate([
                'name' => $employmentType,
            ]);
        }
    }
}
