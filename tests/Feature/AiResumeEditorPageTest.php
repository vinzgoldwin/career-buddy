<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AiResumeEditorPageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_renders_the_resume_editor_component()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('ai-resume-builder.editor'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page
            ->component('ai/ResumeEditor')
            ->has('prefillData')
        );
    }
}
