<?php

namespace Modules\Gamification\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RewardResource extends JsonResource
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
            'mission_id' => $this->mission_id,
            'mission' => $this->when($this->relationLoaded('mission'), function () {
                return [
                    'id' => $this->mission->id,
                    'key' => $this->mission->key,
                    'name' => $this->mission->name,
                ];
            }),
            'reward_type' => $this->reward_type,
            'reward_value' => $this->reward_value,
            'reward_meta' => $this->reward_meta,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}