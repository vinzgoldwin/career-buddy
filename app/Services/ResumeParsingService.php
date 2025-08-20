<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class ResumeParsingService
{
    /**
     * Extract resume information from text and structure it into JSON format
     */
    public function extractResumeInformation(string $pdfText): array
    {
        $prompt = $this->createResumeParsingPrompt($pdfText);

        $response = OpenAI::chat()->create([
            'model' => 'openai/gpt-oss-120b',
            'temperature' => 0,
            'max_tokens' => 4000,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        return $response->toArray();
    }

    /**
     * Create the prompt for parsing resume information
     */
    private function createResumeParsingPrompt(string $pdfText): string
    {
        return <<<PROMPT
            You are tasked with extracting information from a resume provided in text format (converted from PDF) and structuring it into a specific JSON format. The text of the resume will be provided in the following XML tags:

            <pdf_text>
            {$pdfText}
            </pdf_text>

            Your goal is to carefully analyze this text and extract relevant information to populate a JSON structure. The JSON should follow this format:

            {
              "name": "",
              "location": "",
              "description": "",
              "headline": "",
              "languages": [],
              "educations": [
                {
                  "start_date": "",
                  "end_date": "",
                  "school": "",
                  "degree": "",
                  "description": "",
                  "grade": "",
                  "field_of_study": ""
                }
              ],
              "experiences": [
                {
                  "start_date": "",
                  "end_date": null,
                  "company": "",
                  "title": "",
                  "description": "",
                  "location": "",
                  "currently_working": true,
                  "employment_type": ""
                }
              ],
              "skills": [],
              "license_and_certifications": [
                {
                  "name": "",
                  "issuing_organization": "",
                  "issue_date": "",
                  "expiration_date": "",
                  "credential_id": "",
                  "credential_url": ""
                }
              ],
              "projects": [
                {
                  "name": "",
                  "description": "",
                  "start_date": "",
                  "end_date": null,
                  "url": null,
                  "skills_used": []
                }
              ]
            }

            Follow these steps to extract and structure the information:

            1. Name: Extract the full name of the person from the resume.

            2. Location: Find and extract the location information, typically a city and country or state.

            3. Description: Summarize the person's professional profile or summary if available. This should be a concise paragraph describing their expertise and key achievements.

            4. Headline: Extract or create a brief professional headline based on their current position and key skills.

            5. Languages: Identify any languages mentioned and add them to the languages array.

            6. Education: For each education entry found:
               - Extract start and end dates (use the format MM/YYYY if available, otherwise YYYY)
               - Identify the school name
               - Determine the degree type
               - Extract any description provided
               - Find the grade or GPA if mentioned
               - Identify the field of study

            7. Experiences: For each work experience entry:
               - Extract start and end dates (use the format MM/YYYY if available, otherwise YYYY)
               - Identify the company name
               - Extract the job title
               - Summarize the job description if provided
               - Determine the location if mentioned
               - Set \"currently_working\" to true for the most recent job, false for others
               - Determine the employment type if specified (e.g., \"Full-time\", \"Part-time\", etc.)

            8. Skills: Create a list of all professional skills mentioned in the resume.

            9. Licenses and Certifications: For each certification or license:
               - Extract the name of the certification
               - Identify the issuing organization
               - Find the issue date
               - Determine the expiration date if mentioned
               - Extract any credential ID
               - Include a credential URL if provided

            10. Projects: For each project mentioned:
                - Extract the project name
                - Summarize the project description
                - Determine start and end dates if available
                - Include a URL if mentioned
                - List skills used in the project

            If any information is not found in the resume, leave the corresponding field empty (for strings) or as an empty array (for lists). For date fields that are not applicable or not found, use null.

            After extracting all the information, structure it into the JSON format provided above. Ensure that the JSON is properly formatted and that all fields are included, even if some are empty or null.

            Present your final output within <structured_json> tags. Do not include any explanation or additional text outside of these tags.
            PROMPT;
    }
}
