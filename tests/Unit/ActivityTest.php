<?php

namespace Tests\Unit;

use App\User;
use App\Project;
use App\Activity;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_a_user()
    {
        $project = factory(Project::class)->create();

        $this->assertInstanceOf(
            User::class, 
            $project->activity->first()->user
        );
    }
}
