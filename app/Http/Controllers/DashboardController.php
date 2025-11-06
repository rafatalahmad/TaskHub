<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $assignedTasksCount = $user->tasks->count();
        
        $completedTasksCount = $user->tasks->where('status', 'completed')->count();
       
        $activeProjectsCount = $user->projects->count();

        $ownedProjects = $user->ownedProjects->count();
    
        $overdueTasksCount = $user->tasks
            ->where('status', '!=', 'completed')
            ->where('due_date', '<', Carbon::now())//??
            ->count();

        return response()->json([
            'user' => $user->name,
            'stats' => [
                'assigned_tasks' => $assignedTasksCount,
                'completed_tasks' => $completedTasksCount,
                'active_projects' => $activeProjectsCount,
                'overdue_tasks' => $overdueTasksCount,
                'owned_projects' => $ownedProjects,
            ]
        ]);
    }
}
