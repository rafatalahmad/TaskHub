<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity_Log extends Model
{
        use HasFactory;
        protected $table = 'activities';

    protected $fillable = ['task_id', 'user_id', 'action'];

        public function user()
    {
        return $this->belongsTo(User::class);
    }

            public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function activities()
    {
    return $this->hasMany(Activity_Log::class);
    }

}
