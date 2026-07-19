<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityImageResource extends JsonResource
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
            'city_id' => $this->city_id,
            'image' => asset('storage/'.$this->image),
            'is_cover' => $this->is_cover,
            'sort_order' => $this->sort_order,
            'caption'=>$this->caption,
        ];
    }
}
