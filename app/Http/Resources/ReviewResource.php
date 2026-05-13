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
            'user'=>[
                'id'=>$this->user->id,
                'name'=>$this->user->name,
                'email'=>$this->user->email,
                'phone'=>$this->user->phone,
            ],
            'tags'=>$this->tags->pluck('name'),
            'rating'=>$this->rating,
            'comment'=>$this->comment ?? null,
        ];
    }
}
