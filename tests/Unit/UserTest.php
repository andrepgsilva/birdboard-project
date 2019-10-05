<?php

namespace Tests\Unit;

use App\User;
use App\Project;
use Tests\TestCase;

use Facades\Tests\Setup\ProjectFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UserTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function a_user_has_projects()
    {
        $user = factory(User::class)->create();

        $this->assertInstanceOf(Collection::class, $user->projects);
    }

    /** @test */
    public function a_user_has_accessible_projects()
    {
        $john = $this->signIn();
        
        $project = ProjectFactory::ownedBy($john)->create();

        $this->assertCount(1, $john->accessibleProjects());

        $sally = factory(User::class)->create();
        $nick = factory(User::class)->create();

        $sallyProject = ProjectFactory::ownedBy($sally)->create();
        $sallyProject->invite($john);

        $this->assertCount(2, $john->accessibleProjects());
    }
}
