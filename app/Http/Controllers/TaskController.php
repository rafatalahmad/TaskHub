<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    // public function store(StoreTaskRequest $request)
    // {
    //     $user = $request->user();
    //     $validated = $request->validated();
    //     $validated['owner_id'] = $user->id;
    //     $task = $user->tasks()->create($validated);
    //     return response()->json([
    //         'message' => 'Task created successfully',
    //         'task' => $task
    //     ], 201);    
    // }

    public function store(StoreTaskRequest $request, Project $project)
    {
    $user = $request->user();
    if ($user->id !== $project->owner_id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    // هنا نضيف user_id و project_id يدويًا فقط
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
