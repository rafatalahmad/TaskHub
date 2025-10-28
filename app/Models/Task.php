<?php

namespace App\Models;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Task extends Model implements HasMedia
{
        protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'user_id',
        'project_id',
    ];

    use InteractsWithMedia;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id' );
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id' );
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

        public function activity_log()
    {
        return $this->hasOne(Activity_Log::class);
    }


}
