<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
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
public function index(Project $project)
{
    $user = Auth::user();

    $isOwner = $project->owner_id === $user->id;
    $isMember = $project->users->contains($user->id);

    if (!($isOwner || $isMember)) {
        return response()->json(['message' => 'Unauthorized - not part of this project'], 403);
    }

    if ($isOwner) {
        $tasks = $project->tasks()->with('user')->get();
    } 

    else {
        $tasks = $project->tasks()->where('user_id', $user->id)->with('user')->get();
    }

    return response()->json([
        'project' => $project->name,
        'role' => $isOwner ? 'owner' : 'member',
        'tasks' => $tasks
    ], 200);
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

    return response()->json(['message' => 'Task deleted successfully'], 200);
}
    /**
     * Upload a file and attach it to the specified task.
     */
public function uploadFile(Request $request, Task $task)
{
    $user = $request->user();

    // التحقق من صلاحية المستخدم
    $project = $task->project; // المشروع اللي تتبع له المهمة

    $isOwner = $project->owner_id === $user->id; // المالك
    $isAssignedUser = $task->user_id === $user->id; // المكلّف بالمهمة

    // فقط المالك أو العضو المكلّف بالمهمة مسموح له بالرفع
    if (!($isOwner || $isAssignedUser)) {
        return response()->json(['message' => 'Unauthorized - you cannot upload files for this task'], 403);
    }

    // التحقق من الملف
    $request->validate([
        'file' => 'required|file|max:10240', // 10MB كحد أقصى
    ]);

    // رفع الملف
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



}
