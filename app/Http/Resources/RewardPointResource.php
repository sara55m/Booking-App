<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RewardPointResource extends JsonResource
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
            'type'=>$this->type,
            'points'=>$this->points,
            'description'=>$this->description,
            'payment_id'=>$this->payment_id,
            'created_at'=>$this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
