<?php

namespace Tests\Feature;

use App\Task;
use App\User;
use App\Project;
use Tests\TestCase;
use Facades\Tests\Setup\ProjectFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectTasksTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_cannot_add_tasks_to_projects()
    {
        $project = factory(Project::class)->create();

        $this->post($project->path() . '/tasks')
            ->assertRedirect('login');
    }

    /** @test */
    public function only_the_owner_of_a_project_may_add_tasks()
    {
        $this->signIn();

        $project = factory(Project::class)->create();
        
        $response = $this->post($project->path() . '/tasks', [
            'body' => 'Test Task'
        ])->assertStatus(403);

        $this->assertDatabaseMissing('tasks', ['body' => 'Test Task']);
    }

    /** @test */
    public function only_the_owner_of_a_project_may_update_a_task()
    {
        $this->signIn();

        $project = ProjectFactory::withTasks(4)->create();
        // $project = factory(Project::class)->create();
        // $task = $project->addTask('test task');

        $this->patch($project->tasks[0]->path(), [
            'body' => 'changed'
        ])->assertStatus(403);

        $this->assertDatabaseMissing('tasks', ['body' => 'Test Task']);
    }
    
    /** @test */
    public function a_project_can_have_tasks()
    {
        $project = ProjectFactory::create();

        $this->actingAs($project->owner)->post($project->path() . '/tasks', [
            'body' => 'Test Task'
        ]);

        $this->get($project->path())
            ->assertSee('Test Task');
    }

    /** @test */
    public function a_task_can_be_updated()
    {
        $this->withoutExceptionHandling();

        $project = ProjectFactory::withTasks(3)->create();

        $task = $project->tasks[0];

        $this->actingAs($project->owner)
            ->patch($task->path(), [
                'body' => 'changed',
            ]);

        $this->assertDatabaseHas('tasks', [
            'body' => 'changed',
        ]);
    }

    /** @test */
    public function a_task_can_be_completed()
    {
        $this->withoutExceptionHandling();

        $project = ProjectFactory::withTasks(3)->create();

        $task = $project->tasks[0];

        $attributes = [
            'body' => 'changed',
            'completed' => true,
        ];

        $this->actingAs($project->owner)
            ->patch($task->path(), $attributes);

        $this->assertDatabaseHas('tasks', $attributes);
    }

    /** @test */
    function a_task_can_be_marked_as_incomplete()
    {
        $this->withoutExceptionHandling();
        $project = ProjectFactory::withTasks(1)->create();
        $this->actingAs($project->owner)
            ->patch($project->tasks[0]->path(), [
                'body' => 'changed',
                'completed' => true
            ]);
        $this->patch($project->tasks[0]->path(), [
            'body' => 'changed',
            'completed' => false
        ]);
        $this->assertDatabaseHas('tasks', [
            'body' => 'changed',
            'completed' => false
        ]);
    }

    /** @test */
    public function a_task_requires_a_body()
    {
        $project = ProjectFactory::create();

        $attributes = factory(Task::class)->raw([
            'body' => ''
        ]);

        $this->actingAs($project->owner)
            ->post($project->path() . '/tasks', $attributes)
            ->assertSessionHasErrors('body');
    }
}
