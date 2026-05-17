<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'user_id' => $this->user_id,
            'property' =>[
                    'id' => $this->property->id,
                    'name' => $this->property->name],
            'room' => [
                    'id' => $this->room->id,
                    'name' => $this->room->name,
                    'number' => $this->room->number
            ],
            'check_in' => $this->check_in->format('Y-m-d'),
            'check_out' => $this->check_out->format('Y-m-d'),
            'guests_count' => $this->guests_count,
            'nights_count' => $this->nights_count,
            'offer'=>$this->offer ? [
                'id'=>$this->offer?->id,
                'title'=>$this->offer->title,
                'discount_value'=>$this->offer->discount_value,
                'discount_type'=>$this->offer->discount_type
            ] : null,
            'original_price'=>$this->original_price.' EGP',
            'discount_amount'=>$this->discount_amount,
            'total_price' => $this->total_price.' EGP',
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
