<?php

Route::group(['middleware' => 'auth'], function() {
    Route::get('/', 'ProjectsController@index');
    
    Route::resource('projects', 'ProjectsController');
    
    Route::post('/projects/{project}/tasks', 'ProjectTasksController@store');

    Route::patch('/projects/{project}/tasks/{task}', 'ProjectTasksController@update');

    Route::delete('/projects/{project}/tasks/{task}', 'ProjectTasksController@destroy');

    Route::post('/projects/{project}/invitations', 'ProjectInvitationsController@store');
    
    Route::get('/home', 'HomeController@index')->name('home');
});

// Route::post('/projects', function () {
//     App\Project::create(request(['title', 'description']));
// });

Auth::routes();

