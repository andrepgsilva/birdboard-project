<?php

namespace Tests\Feature;

use App\User;
use App\Project;
use Tests\TestCase;
use Facades\Tests\Setup\ProjectFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManageProjectsTest extends TestCase
{
    use withFaker, RefreshDatabase;

    /** @test */
    public function a_user_can_create_project()
    {
        $this->withoutExceptionHandling();

        $this->signIn();

        $attributes = factory(Project::class)->raw();

        $this->followingRedirects()->post('/projects', $attributes)
            ->assertSee($attributes['title'])
            ->assertSee($attributes['description'])
            ->assertSee($attributes['notes']);
    }

    /** @test */
    public function tests_can_be_included_as_part_a_new_project_creation()
    {
        $this->signIn();

        $attributes = factory(Project::class)->raw();

        $attributes['tasks'] = [
            ['body' => 'Task 1'],
            ['body' => 'Task 2'],
        ];

        $this->post('/projects', $attributes);

        $this->assertCount(2, Project::first()->tasks);
    }

    /** @test */
    public function a_project_requires_a_title()
    {
        $user = factory(User::class)->create();
        $this->signIn();

        $attributes = factory(Project::class)->raw(['title' => '']);

        $response = $this->post('/projects', $attributes);

        $response->assertSessionHasErrors('title');
    }

    /** @test */
    public function a_project_requires_a_description()
    {
        $user = factory(User::class)->create();
        $this->signIn();

        $attributes = factory(Project::class)->raw(['description' => '']);

        $response = $this->post('/projects', $attributes);

        $response->assertSessionHasErrors('description');
    }

    /** @test */
    public function a_user_can_view_all_projects_they_have_been_invited_on_their_dashboard()
    {
        $user = $this->signIn();

        $project = ProjectFactory::create();

        $project->invite($user);

        $this->get('projects/')
            ->assertSee($project->title);
    }

    /** @test */
    public function unauthorized_users_cannot_delete_projects()
    {
        $project = ProjectFactory::create();

        $response = $this->delete($project->path())
            ->assertRedirect('/login');

        $user = $this->signIn();

        $this->delete($project->path())
            ->assertStatus(403);

        $project->invite($user);

        $this->actingAs($user)->delete($project->path())
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_can_delete_a_project()
    {
        $this->withoutExceptionHandling();

        $project = ProjectFactory::create();

        $this->actingAs($project->owner);

        $response = $this->delete($project->path());

        $response->assertRedirect('/projects');

        $this->assertDatabaseMissing('projects', [
            $project->only('id')
        ]);
    }

    /** @test */
    public function a_user_can_update_a_project()
    {
        $project = ProjectFactory::create();

        $this->actingAs($project->owner);

        $response = $this->patch(
            $project->path(),
            $attributes = [
                'title' => 'Changed',
                'description' => 'Changed',
                'notes' => 'Changed',
            ]
        );

        $response->assertRedirect($project->path());

        $this->get($project->path() . '/edit')
            ->assertOk();

        $this->assertDatabaseHas('projects', $attributes);
    }

    /** @test */
    public function a_user_can_update_a_projects_general_notes()
    {
        $project = ProjectFactory::create();

        $this->actingAs($project->owner)
            ->patch(
                $project->path(),
                $attributes = ['notes' => 'Changed',]
            );

        $this->assertDatabaseHas('projects', $attributes);
    }

    /** @test */
    public function a_user_can_view_their_project()
    {
        $project = ProjectFactory::create();

        $r = $this->actingAs($project->owner)
            ->get($project->path())
            ->assertSee($project->title);
    }

    /** @test */
    public function project_requires_an_owner()
    {
        $attributes = factory(Project::class)->raw();

        $response = $this->post('/projects', $attributes);

        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    /** @test */
    public function guests_cannot_create_a_project()
    {
        $user = factory(User::class)->create();

        $attributes = factory(Project::class)->raw();

        $response = $this->post('/projects', $attributes);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function guest_cannot_view_projects()
    {
        $response = $this->get('/projects');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function guest_cannot_view_create_page()
    {
        $response = $this->get('/projects/create');

        $response->assertStatus(302);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function guest_cannot_view_a_single_project()
    {
        $project = factory(Project::class)->create();

        $this->get($project->path())->assertRedirect('login');
    }

    /** @test */
    public function an_authenticated_user_cannot_view_the_projects_of_others()
    {
        $this->signIn();

        $project = factory(Project::class)->create();

        $this->get($project->path())->assertStatus(403);
    }

    /** @test */
    public function an_authenticated_user_cannot_update_the_projects_of_others()
    {
        $project = ProjectFactory::create();
        $projectAnotherPerson = ProjectFactory::create();

        $this->actingAs($project->owner)
            ->get($projectAnotherPerson->path())
            ->assertStatus(403);

        $this->patch($projectAnotherPerson->path(), $attributes = ['notes' => 'Changed',])
            ->assertStatus(403);

        $this->assertDatabaseMissing('projects', $attributes);
    }


    /** @test */
    public function a_user_can_view_the_projects_shared_with_it()
    {
        $project = ProjectFactory::create();

        $user = factory(User::class)->create();

        $project->invite($user);

        $this->actingAs($user);

        $response = $this->get($project->path());

        $response->assertOk();
    }
}
