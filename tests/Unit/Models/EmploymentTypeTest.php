<?php

namespace Tests\Unit\Models;

use App\Models\EmploymentType;
use App\Models\Experience;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmploymentTypeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_employment_types()
    {
        $employmentType = EmploymentType::create(['name' => 'Full-time']);

        $this->assertDatabaseHas('employment_types', [
            'name' => 'Full-time',
        ]);

        $this->assertEquals('Full-time', $employmentType->name);
    }

    #[Test]
    public function it_can_associate_employment_type_with_experience()
    {
        $user = User::factory()->create();
        $employmentType = EmploymentType::create(['name' => 'Full-time']);

        $experience = Experience::create([
            'user_id' => $user->id,
            'title' => 'Software Engineer',
            'company' => 'Tech Corp',
            'start_date' => '2020-01-01',
            'employment_type_id' => $employmentType->id,
        ]);

        $this->assertDatabaseHas('experiences', [
            'title' => 'Software Engineer',
            'company' => 'Tech Corp',
            'employment_type_id' => $employmentType->id,
        ]);

        $this->assertInstanceOf(EmploymentType::class, $experience->employmentType);
        $this->assertEquals('Full-time', $experience->employmentType->name);
    }

    #[Test]
    public function it_can_have_null_employment_type()
    {
        $user = User::factory()->create();

        $experience = Experience::create([
            'user_id' => $user->id,
            'title' => 'Software Engineer',
            'company' => 'Tech Corp',
            'start_date' => '2020-01-01',
            'employment_type_id' => null,
        ]);

        $this->assertDatabaseHas('experiences', [
            'title' => 'Software Engineer',
            'company' => 'Tech Corp',
            'employment_type_id' => null,
        ]);

        $this->assertNull($experience->employmentType);
    }
}