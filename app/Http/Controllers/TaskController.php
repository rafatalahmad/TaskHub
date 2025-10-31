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

    // تحقق إن المستخدم داخل المشروع
    $isOwner = $project->owner_id === $user->id;
    $isMember = $project->users->contains($user->id);

    if (!($isOwner || $isMember)) {
        return response()->json(['message' => 'Unauthorized - not part of this project'], 403);
    }

    // إذا المالك -> يشوف كل المهام
    if ($isOwner) {
        $tasks = $project->tasks()->with('user')->get();
    } 
    // إذا عضو -> يشوف فقط مهامه الخاصة
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

    // تحقق أن المستخدم هو مالك المشروع
    if ($user->id !== $project->owner_id) {
        return response()->json(['message' => 'Unauthorized - only the project owner can add tasks'], 403);
    }

    // التحقق من البيانات
    $validated = $request->validated();

    // تحقق أن المستخدم المُعيّن موجود داخل المشروع
    if (!$project->users->contains($validated['user_id'])) {
        return response()->json(['message' => 'User not part of this project'], 400);
    }

    // إنشاء المهمة وتعيينها للمستخدم المحدد
    $task = $project->tasks()->create([
        'title' => $validated['title'],
        'description' => $validated['description'] ?? null,
        'priority' => $validated['priority'] ?? 'medium',
        'due_date' => $validated['due_date'] ?? null,
        'status' => $validated['status'] ?? 'pending',
        'user_id' => $validated['user_id'], // تعيين المستخدم
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

    // تحقق إن المستخدم هو المالك فقط
    if ($project->owner_id !== $user->id) {
        return response()->json(['message' => 'Unauthorized - only project owner can update tasks'], 403);
    }

    // ابحث عن المهمة داخل المشروع
    $task = $project->tasks()->where('id', $id)->first();

    if (!$task) {
        return response()->json(['message' => 'Task not found'], 404);
    }

    // التحقق من القيم الجديدة (validation)
    $validated = $request->validated();

    // لو المالك بدو يغيّر المستخدم المعيّن للمهمة
    if (isset($validated['user_id'])) {
        // تأكد أن المستخدم موجود ضمن أعضاء المشروع
        if (!$project->users->contains($validated['user_id'])) {
            return response()->json(['message' => 'User not part of this project'], 400);
        }
    }

    // تنفيذ التحديث
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

    // تحقق إن المستخدم هو المالك فقط
    if ($project->owner_id !== $user->id) {
        return response()->json(['message' => 'Unauthorized - only project owner can delete tasks'], 403);
    }

    // ابحث عن المهمة داخل المشروع
    $task = $project->tasks()->where('id', $id)->first();

    if (!$task) {
        return response()->json(['message' => 'Task not found'], 404);
    }

    // احذف المهمة
    $task->delete();

    return response()->json(['message' => 'Task deleted successfully'], 200);
}

}
