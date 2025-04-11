<?php

namespace Salla\Gamification\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $taskData = [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'points' => $this->points,
            'icon' => $this->icon,
            'event_name' => $this->event_name,
            'is_active' => $this->is_active,
        ];

        // Add completion status if available
        if (isset($this->completion)) {
            $taskData['completion'] = [
                'status' => $this->completion ? $this->completion->status : 'not_started',
                'completed_at' => $this->completion && $this->completion->completed_at ? $this->completion->completed_at->toIso8601String() : null,
            ];
        }

        return $taskData;
    }
}