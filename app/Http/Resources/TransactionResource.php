<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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

            'booking'=>[
                'id'=>$this->booking->id
            ],
            'property'=>[
                'id'=>$this->booking->property->id,
                'name'=>$this->booking->property->name,
            ],
            'amount' => $this->amount,

            'remaining' => $this->remaining,

            'currency'=>$this->currency,

            'refunded_amount'=>$this->refunded_amount,

            'status' => $this->status,

            'payment_method' => $this->payment_method,

            'transaction_id' => $this->transaction_id,

            'paid_at' => optional($this->paid_at)->format('Y-m-d H:i:s'),

            'refunded_at'=>optional($this->refunded_at)->format('Y-m-d H:i:s'),

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            ];
    }
}
