<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'due_date'
        
    ];

    public function users()
    {
        return $this->belongsToMany(User::class,'user__projects', 'project_id', 'user_id');
    }

    public function owner()
    {
    return $this->belongsTo(User::class, 'owner_id');
    }


    public function tasks()
    {
        return $this->hasMany(Task::class,'project_id');
    }
}
