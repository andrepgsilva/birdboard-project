<?php

namespace App\Http\Controllers;

use App\Task;
use App\Project;
use Illuminate\Http\Request;

class ProjectTasksController extends Controller
{
    public function store(Project $project)
    {
        $this->authorize('update', $project);

        request()->validate(['body' => 'required']);
        $project->addTask(request('body'));

        return redirect($project->path());
    }

    public function update(Project $project, Task $task)
    {
        $this->authorize('update', $project);

        $task->update(request()->validate([
            'body' => 'required'
        ]));

        request('completed') ? $task->complete() : $task->incomplete();

        return redirect($project->path());
    }

    public function destroy(Project $project, Task $task)
    {
        $this->authorize('update', $project);
        
        // $response = $this->delete($task->path());
        $task->delete();

        return redirect($project->path());
    }
}
