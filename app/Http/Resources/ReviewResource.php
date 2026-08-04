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
            'property' => $this->whenLoaded('property', function () {
                return [
                    'id' => $this->property->id,
                    'name' => $this->property->name,
                    'address'=>$this->property->address,
                ];
            }),

            'booking' => $this->whenLoaded('booking', function () {
                return [
                    'id' => $this->booking->id,
                    'reference'=>$this->booking->reference,
                    'check_in' => $this->booking->check_in->format('Y-m-d'),
                    'check_out' => $this->booking->check_out->format('Y-m-d'),
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'rating' => $category->pivot->rating,
                ])->values();
            }),
            'tags' => $this->whenLoaded('tags', function () {
                return $this->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'type'=>$tag->type,
                ])->values();
            }),
            'rating'=>$this->rating,
            'comment'=>$this->comment ?? null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
