<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'Project title' => $this->project->title,
            'Owner project' => $this->project->owner->name,
            'Assigned To' => $this->user->name,
            'Title' => $this->title,
            'Description' => $this->description,
            'Priority' => $this->priority,
            'Status' => $this->status,
            'Due Date' => $this->due_date,
            'Created at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
