<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Task $task)
{
    $comments = $task->comments()->with('user')->latest()->get();

    return response()->json(['comments' => $comments]);
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
public function store(Request $request, Task $task)
{
    $request->validate([
        'content' => 'required|string|max:500',
    ]);

    $user = Auth::user();

    $comment = Comment::create([
        'task_id' => $task->id,
        'user_id' => $user->id,
        'content' => $request->content,
    ]);
    // إشعار لصاحب المهمة
    $taskOwner = $task->user; // المستخدم المعيّن للمهمة
    if ($taskOwner && $taskOwner->id !== $user->id) {
       $taskOwner->notify(new NewCommentNotification($comment));
   }

    return response()->json(['message' => 'Comment added successfully', 'comment' => $comment], 201);
    $comment = Comment::create([
    'task_id' => $task->id,
    'user_id' => $user->id,
    'content' => $request->content,
]);



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
    public function update(Request $request, Task $task, Comment $comment)
    {
        $user = Auth::user();

        
        if ($comment->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized - you can only edit your own comment'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $comment->update(['content' => $request->content]);

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $comment
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
 public function destroy(Task $task, Comment $comment)
{
    $user = Auth::user();
    $project = $task->project; 
    
    $isOwner = $project->owner_id === $user->id; 
    $isCommentOwner = $comment->user_id === $user->id; 

    if (!($isOwner || $isCommentOwner)) {
        return response()->json(['message' => 'Unauthorized - only comment owner or project owner can delete this comment'], 403);
    }

    $comment->delete();

    return response()->json(['message' => 'Comment deleted successfully'], 200);
}

}
