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
    try {
            $user = Auth::user();

    $isOwner = $project->owner_id === $user->id;
    $isMember = $project->users->contains($user->id);

    if (!($isOwner || $isMember)) {
        return response()->json(['message' => 'Unauthorized - not part of this project'], 403);
    }
    $tasks = $project->tasks()->with('project')->get();
    return response()->json([
        'project' => $project->name,
        'tasks' => $tasks
    ], 200);
    } 
    catch (\Exception $e) {
        return response()->json(['message' => 'Project not found'], 404);
    }

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
        return response()->json(['message' => 'Unauthorized ليس لديك مشروع'], 403);
    }
    
    $validated = $request->validated();
    $validated['user_id'] = $user->id;
    $validated['project_id'] = $project->id;
    $task = Task::create($validated);
    return response()->json(['message' => 'Task created successfully',
     'task' => $task], 201);
   }
    /**
     * Display the specified resource.
     */

// public function show(Project $project)
// {
//     $user = Auth::user();

//     // تحقق إن المستخدم ضمن المشروع (مالك أو عضو)
//     $isOwner = $project->owner_id === $user->id;
//     $isMember = $project->users->contains($user->id);

//     if (!($isOwner || $isMember)) {
//         return response()->json(['message' => 'Unauthorized - not part of this project'], 403);
//     }

//     // المالك يشوف كل المهام
//     if ($isOwner) {
//         $tasks = $project->tasks()->get();
//     } 
//     // العضو يشوف مهامه فقط
//     else {
//         $tasks = $project->tasks()->where('user_id', $user->id)->get();
//     }

//     return response()->json([
//         'project' => $project->name,
//         'message' => $isOwner ? 'All project tasks' : 'Your tasks in this project',
//         'tasks' => $tasks
//     ], 200);
// }


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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
