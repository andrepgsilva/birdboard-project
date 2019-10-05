<?php

use App\Project;
use Illuminate\Database\Seeder;
use Facades\Tests\Setup\ProjectFactory;

class ProjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=0; $i < 3; $i++) { 
            ProjectFactory::withTasks(5)->create();
        }

        $user = Project::first()->owner;
        $user->email = 'eloisa49@example.com';
        $user->save();
    }
}
