<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Models\Project;
use App\Models\Task;
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

}
