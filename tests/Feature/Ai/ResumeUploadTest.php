<?php

namespace Tests\Feature\Ai;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResumeUploadTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_upload_pdf_resume(): void
    {
        // Create a fake PDF file for testing
        Storage::fake('local');
        $file = UploadedFile::fake()->createWithContent('resume.pdf', '%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj

2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj

3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 4 0 R
/Resources <<
/ProcSet [/PDF /Text]
/Font <<
/F1 5 0 R
>>
>>
>>
endobj

4 0 obj
<<
/Length 44
>>
stream
BT
/F1 24 Tf
100 700 Td
(John Doe Resume) Tj
ET
endstream
endobj

5 0 obj
<<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica
>>
endobj

xref
0 6
0000000000 65535 f
0000000010 00000 n
0000000053 00000 n
0000000114 00000 n
0000000235 00000 n
0000000323 00000 n
trailer
<<
/Size 6
/Root 1 0 R
>>
startxref
423
%%EOF');

        // Authenticate the user (assuming you have authentication set up)
        $user = User::factory()->create();
        $this->actingAs($user);

        // Make a request to upload the resume
        $response = $this->post(route('ai-resume-builder.upload'), [
            'resume' => $file,
        ]);

        // Assert the response
        $response->assertStatus(200);
        $response->assertJsonStructure(['text']);
        $response->assertJsonFragment(['text' => 'John Doe Resume']);
    }

    #[Test]
    public function user_cannot_upload_non_pdf_files(): void
    {
        // Authenticate the user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a fake non-PDF file
        $file = UploadedFile::fake()->create('document.txt', 100);

        // Make a request to upload the non-PDF file
        $response = $this->post(route('ai-resume-builder.upload'), [
            'resume' => $file,
        ]);

        // Assert the response
        $response->assertStatus(302); // Redirect back with errors
        $response->assertSessionHasErrors(['resume']);
    }

    #[Test]
    public function user_cannot_upload_pdf_larger_than_10mb(): void
    {
        // Authenticate the user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a fake PDF file larger than 10MB
        $file = UploadedFile::fake()->create('large_resume.pdf', 11000, 'application/pdf');

        // Make a request to upload the large PDF file
        $response = $this->post(route('ai-resume-builder.upload'), [
            'resume' => $file,
        ]);

        // Assert the response
        $response->assertStatus(302); // Redirect back with errors
        $response->assertSessionHasErrors(['resume']);
    }
}
