<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{

    public function users()
    {
        return $this->belongsToMany(User::class,'user__projects');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
