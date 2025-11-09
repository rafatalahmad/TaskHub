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

        // عدد المهام
        $totalTasks = $user->tasks->count();
        $completedTasks = $user->tasks->where('status', 'completed')->count();
        $activeProjects = $user->projects->count() + $user->ownedProjects->count();
        $overdueTasks = $user->tasks
            ->where('status', '!=', 'completed')
            ->where('due_date', '<', Carbon::now())
            ->count();

        $productivity = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

        $quotes = [
            "ابدأ يومك بطاقة إيجابية وهدف واضح!",
            "كل خطوة صغيرة اليوم تقرّبك من حلمك.",
            "الإنجاز لا يحتاج حظًا، بل استمرارية.",
            "أنت أقوى مما تعتقد، استمر!"
        ];
        $randomQuote = $quotes[array_rand($quotes)];

        $streakDays = $user->streak_days ?? 0;

        return response()->json([
            'user' => $user->name,
            'stats' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'productivity_score' => $productivity,
                'active_projects' => $activeProjects,
                'overdue_tasks' => $overdueTasks,
                'daily_streak_days' => $streakDays,
            ],
            'quote_of_the_day' => $randomQuote,
        ]);
    }
}
