<?php

namespace Tests\Feature;

use App\Task;
use App\Activity;
use Tests\TestCase;
use Facades\Tests\Setup\ProjectFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TriggerActivityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function creating_a_project_records_activity()
    {
        $project = ProjectFactory::create();

        tap($project->activity->last(), function($activity) {
            $this->assertEquals('created_project', $activity->description);
            
            $this->assertNull($activity->changes);
        });
    }

    /** @test */
    public function updating_a_project_records_activity()
    {
        $project = ProjectFactory::create();
        $originalTitle = $project->title;

        $project->update([
            'title' => 'Changed'
        ]);
        
        $this->assertCount(2, $project->activity);

        tap($project->activity->last(), function($activity) use ($originalTitle){
            $this->assertEquals(
                'updated_project',
                $activity->description
            );

            $expected = [
                'before' => ['title' => $originalTitle],
                'after' => ['title' => 'Changed']
            ];

            $this->assertEquals($expected, $activity->changes);
        });
    }

    /** @test */
    public function creating_a_new_task_records_project_activity()
    {
        $project = ProjectFactory::create();
        $project->addTask('Some task');
        
        $this->assertCount(2, $project->activity);
        tap($project->activity->last(), function ($activity) {
            $this->assertEquals('created_task', $activity->description);
            $this->assertInstanceOf(Task::class, $activity->subject);
            $this->assertEquals('Some task', $activity->subject->body);
        });
    }

    /** @test */
    public function completing_a_task_records_project_activity()
    {
        $project = ProjectFactory::withTasks(1)->create();

        $this->actingAs($project->owner)
            ->patch($project->tasks[0]->path(), [
                'body' => 'foobar',
                'completed' => true
            ]);

        $this->assertCount(3, $project->activity);

        tap($project->activity->last(), function($activity){
            $this->assertEquals('completed_task', $activity->description);
            $this->assertInstanceOf(Task::class, $activity->subject);
        });
    }

    /** @test */
    public function incompleting_a_task_records_project_activity()
    {
        $project = ProjectFactory::withTasks(1)->create();

        $this->actingAs($project->owner)
            ->patch($project->tasks[0]->path(), [
                'body' => 'foobar',
                'completed' => true
            ]);

        $this->assertCount(3, $project->activity);

        $this->patch($project->tasks[0]->path(), [
            'body' => 'foobar',
            'completed' => false
        ]);
        
        $project->refresh();

        $task = $project->tasks[0];
        $taskLastActivity = $task->activity()->latest('id')->get();

        $this->assertCount(4, $project->activity);
        
        $this->assertEquals(
            'incompleted_task',
            $taskLastActivity->first()->description
        );
    }

    /** @test */
    public function deleting_a_task_records_project_activity()
    {
        $this->withoutExceptionHandling();

        $project = ProjectFactory::withTasks(1)->create();

        $response = $this->actingAs($project->owner)
            ->delete($project->tasks[0]->path());

        $response->assertRedirect($project->path());
        
        $this->assertCount(3, $project->activity);
    }
}
