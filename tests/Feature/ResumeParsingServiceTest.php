<?php

use App\Services\ResumeParsingService;

test('it can extract resume information', function () {
    // Skip this test since it requires an actual OpenAI API key
    $this->markTestSkipped('OpenAI API key required for this test');

    // If you want to run this test, remove the markTestSkipped line and provide a real API key
    $service = new ResumeParsingService;

    $sampleResume = 'John Doe
123 Main Street, New York, NY 10001
johndoe@email.com | (555) 123-4567

PROFESSIONAL SUMMARY
Experienced software engineer with 5 years of experience in web development.

WORK EXPERIENCE
Software Engineer
Tech Solutions Inc., New York, NY
06/2020 - Present
- Developed web applications using PHP and Laravel
- Collaborated with cross-functional teams

EDUCATION
Bachelor of Science in Computer Science
University of New York, New York, NY
09/2015 - 05/2019

SKILLS
PHP, Laravel, JavaScript, HTML, CSS';

    $result = $service->extractResumeInformation($sampleResume);

    expect($result)->toBeArray();
    expect($result)->toHaveKey('choices');
});
