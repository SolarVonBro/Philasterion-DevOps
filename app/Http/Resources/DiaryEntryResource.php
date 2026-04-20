<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiaryEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'recorded_at' => $this->recorded_at->toDateString(),
            'mood'        => $this->mood,
            'energy'      => $this->energy,
            'sleep_hours' => $this->sleep_hours,
            'notes'       => $this->notes,
            'created_at'  => $this->created_at,
        ];
    }
}