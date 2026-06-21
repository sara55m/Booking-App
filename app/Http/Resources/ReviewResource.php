<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'property_id'=>$this->property_id,
            'booking_id'=>$this->booking_id,
            'user'=>[
                'id'=>$this->user->id,
                'name'=>$this->user->name,
            ],
            'tags' => $this->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            })->values(),
            'rating'=>$this->rating,
            'comment'=>$this->comment ?? null,
        ];
    }
}
