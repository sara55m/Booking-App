<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_id' => $this->room_id,
            'image' => asset('storage/'.$this->image),
            'is_cover' => $this->is_cover,
            'sort_order' => $this->sort_order,
            'caption'=>$this->caption,
        ];
    }
}
