<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PdfExtractionTest extends TestCase
{
    #[Test]
    public function user_can_extract_text_from_pdf(): void
    {
        // Create a fake PDF file for testing
        Storage::fake('local');
        $file = UploadedFile::fake()->createWithContent('document.pdf', '%PDF-1.4
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
(Hello World) Tj
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
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // Make a request to extract text from the PDF
        $response = $this->post(route('pdf.extract-text'), [
            'pdf' => $file,
        ]);

        // Assert the response
        $response->assertStatus(200);
        $response->assertJsonStructure(['text']);
        $response->assertJsonFragment(['text' => 'Hello World']);
    }
}