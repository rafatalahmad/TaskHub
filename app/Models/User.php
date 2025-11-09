<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class,'user__projects', 'user_id', 'project_id');
    }

    public function activitys_logs()
    {
        return $this->hasMany(Activity_Log::class);
    }

    public function ownedProjects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }
    public function ownedTasks()
    {
    return $this->hasManyThrough(
        Task::class,     
        Project::class,
        'owner_id',      
        'project_id',    
        'id',            
        'id'        
    );
    }

    public function getProductivityScoreAttribute()
{
    $totalTasks = $this->tasks()->count();
    $completedTasks = $this->tasks()->where('status', 'completed')->count();

    if ($totalTasks === 0) {
        return 0;
    }

    return round(($completedTasks / $totalTasks) * 100, 2); // نسبة مئوية
}


}
