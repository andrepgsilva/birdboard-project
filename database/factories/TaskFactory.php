<?php

use App\Task;
use App\Project;
use Faker\Generator as Faker;

$factory->define(Task::class, function (Faker $faker) {
    return [
        'body' => $faker->sentence,
        'project_id' => function() {
            return factory(Project::class)->create()->id;
        },
        'completed' => false
    ];
});
