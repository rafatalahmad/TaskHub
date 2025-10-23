<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
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
    public function store(StoreProjectRequest $request)
    {
        $user_id = Auth::user()->id;
        $validated = $request->validated();
        $validated['owner_id'] = $user_id;
        $project = Project::create($validated);
        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project
        ], 201);

        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user_id = Auth::user()->id;
        $project = Project::where('id', $id)->where('owner_id', $user_id)->first();
        if (!$project) 
            return response()->json([
                'message' => 'Project not found',
                'your projects number'=>Project::where('owner_id', $user_id)->pluck('id')->toArray()
            ], 404);        
        return response()->json([
            'project' => $project
        ], 200);    
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
    public function update(UpdateProjectRequest $request, string $id)
    {
        $user_id = Auth::user()->id;
        $validated=$request->validated();
        $project = Project::where('id', $id)->where('owner_id', $user_id)->first();
        if (!$project) 
            return response()->json([
                'message' => 'Project not found',
                'your projects number'=>Project::where('owner_id', $user_id)->pluck('id')->toArray()
            ], 404);        
        $project->update($validated);
        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user_id = Auth::user()->id;
        $project = Project::where('id', $id)->where('owner_id', $user_id)->first();
        if (!$project) 
            return response()->json([
                'message' => 'Project not found',
                'your projects number'=>Project::where('owner_id', $user_id)->pluck('id')->toArray()
            ], 404);        
        $project->delete();
        return response()->json([
            'message' => 'Project deleted successfully'
        ], 200);
    }
}
