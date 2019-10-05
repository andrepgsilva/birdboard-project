<?php

namespace App;

use App\User;
use App\Project;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $guarded = [];

    protected $casts = [
        'changes' => 'array'
    ];

    public function project()
    {
        return belongsTo(Project::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
