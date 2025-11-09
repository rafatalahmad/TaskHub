<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TaskResources;
use App\Models\Activity_Log;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */

public function index(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;

        $ownedProjectIds = $user->ownedProjects()->pluck('id');

        $memberProjectIds = $user->projects()->pluck('projects.id');
        
        $query = Task::with('project.owner', 'user') 
            ->where(function ($q) use ($ownedProjectIds, $memberProjectIds, $userId) {

                
                $q->whereIn('project_id', $ownedProjectIds);

            
                $q->orWhere(function ($subQuery) use ($memberProjectIds, $userId) {
                    $subQuery->whereIn('project_id', $memberProjectIds)
                             ->where('user_id', $userId); 
                });
            });

        $query->when($request->has('status'), function ($q) use ($request) {
            return $q->where('status', $request->input('status'));
        });
      
        $query->when($request->has('priority'), function ($q) use ($request) {
            return $q->where('priority', $request->input('priority'));
        });

        $query->when($request->has('search'), function ($q) use ($request) {
            $searchTerm = $request->input('search');
            return $q->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('title', 'like', "%{$searchTerm}%")
                         ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        });
        
        $tasks = $query->latest()->paginate(10); 

        return TaskResources::collection($tasks);

    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     */

public function store(StoreTaskRequest $request, Project $project)
{
    $user = $request->user();

    if ($user->id !== $project->owner_id) {
        return response()->json(['message' => 'Unauthorized - only the project owner can add tasks'], 403);
    }


    $validated = $request->validated();

    if (!$project->users->contains($validated['user_id'])) {
        return response()->json(['message' => 'User not part of this project'], 400);
    }

    $task = $project->tasks()->create([
        'title' => $validated['title'],
        'description' => $validated['description'] ?? null,
        'priority' => $validated['priority'] ?? 'medium',
        'due_date' => $validated['due_date'] ?? null,
        'status' => $validated['status'] ?? 'pending',
        'user_id' => $validated['user_id'], 
    ]);
    Activity_Log::create([
    'task_id' => $task->id,
    'user_id' => Auth::id(),
    'action' => 'created', 
    ]);

    $assignedUser = User::find($validated['user_id']);
    if ($assignedUser) {
         $assignedUser->notify(new TaskAssignedNotification($task));
    }

    return response()->json(['message' => 'Task assigned successfully', 'task' => $task], 201);


}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
public function update(StoreTaskRequest $request, Project $project, string $id)
{
    $user = Auth::user();

    if ($project->owner_id !== $user->id) {
        return response()->json(['message' => 'Unauthorized - only project owner can update tasks'], 403);
    }

    $task = $project->tasks()->where('id', $id)->first();

    if (!$task) {
        return response()->json(['message' => 'Task not found'], 404);
    }

    $validated = $request->validated();

    if (isset($validated['user_id'])) {

        if (!$project->users->contains($validated['user_id'])) {
            return response()->json(['message' => 'User not part of this project'], 400);
        }
    }

    $task->update($validated);
    Activity_Log::create([
    'task_id' => $task->id,
    'user_id' => Auth::id(),
    'action' => 'updated',
    ]);

    return response()->json([
        'message' => 'Task updated successfully by project owner',
        'task' => $task
    ], 200);
}


    /**
     * Remove the specified resource from storage.
     */
  public function destroy(Project $project, string $id)
{
    $user = Auth::user();

    if ($project->owner_id !== $user->id) {
        return response()->json(['message' => 'Unauthorized - only project owner can delete tasks'], 403);
    }

    $task = $project->tasks()->where('id', $id)->first();

    if (!$task) {
        return response()->json(['message' => 'Task not found'], 404);
    }

    $task->delete();
    Activity_Log::create([
    'task_id' => $task->id,
    'user_id' => Auth::id(),
    'action' => 'deleted', 
    ]);

    return response()->json(['message' => 'Task deleted successfully'], 200);
}
    /**
     * Upload a file and attach it to the specified task.
     */
public function uploadFile(Request $request, Task $task)
{
    $user = $request->user();

    $project = $task->project;

    $isOwner = $project->owner_id === $user->id; 
    $isAssignedUser = $task->user_id === $user->id; 

    if (!($isOwner || $isAssignedUser)) {
        return response()->json(['message' => 'Unauthorized - you cannot upload files for this task'], 403);
    }

    $request->validate([
        'file' => 'required|file|max:10240', 
    ]);

    $media=$task->addMediaFromRequest('file')->toMediaCollection('attachments');

    return response()->json([
        'message' => 'File uploaded successfully',
        'id' => $media->id,
        'file_name' => $media->file_name,
        'url' => $media->getUrl(),
        'created_at' => $media->created_at,
        'size' => $media->size,

    ], 200);
}

public function activityLog(Task $task)
{
    $logs = $task->activities()->with('user')->latest()->get();

    return response()->json(['activity' => $logs]);
}

public function updateStatus(Request $request, Task $task)
{
    $user = Auth::user();

    $data = $request->validate([
        'status' => 'required|string|in:pending,in-progress,completed',
    ]);

    $isAssignedUser = $task->user_id == $user->id;

    if (!$isAssignedUser) {
        return response()->json(['message' => 'You are not authorized to update this task status.'], 403);
    }

    $oldStatus = $task->status;
    $newStatus = $data['status'];

    $task->update(['status' => $newStatus]);

    if ($oldStatus == $newStatus) {
        return response()->json('No status change detected.', 200);
    }

    if ($newStatus === 'completed') {
        $today = now()->toDateString();
        $lastDate = $user->last_completed_task_date;

        if ($lastDate === $today) {
        } elseif ($lastDate === now()->subDay()->toDateString()) {
            $user->streak_days += 1;
            $user->last_completed_task_date = $today;
        } else {
            $user->streak_days = 1;
            $user->last_completed_task_date = $today;
        }
        $user->save;
    }

    Activity_Log::create([
        'task_id' => $task->id,
        'user_id' => $user->id, 
        'action' => "changed status from '{$oldStatus}' to '{$newStatus}'"
    ]);

    return response()->json($task);
}



}
