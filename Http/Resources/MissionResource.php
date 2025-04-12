<?php

namespace Modules\Gamification\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'total_points' => $this->total_points,
            'is_active' => $this->is_active,
            'start_date' => $this->start_date ? $this->start_date->toIso8601String() : null,
            'end_date' => $this->end_date ? $this->end_date->toIso8601String() : null,
            'sort_order' => $this->sort_order,
            'tasks' => $this->when($this->relationLoaded('tasks'), function () {
                return TaskResource::collection($this->tasks);
            }),
            'progress' => $this->when($this->relationLoaded('storeProgress') && $this->storeProgress->isNotEmpty(), function () {
                $progress = $this->storeProgress->first();
                return [
                    'status' => $progress->status,
                    'progress_percentage' => $progress->progress_percentage,
                    'completed_at' => $progress->completed_at ? $progress->completed_at->toIso8601String() : null,
                ];
            }),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}