<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\BookingStatus;
use App\Services\CurrencyService;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currency = strtoupper(
            auth()->user()?->currency ?? config('app.currency', 'USD')
        );

        return [
            'id' => $this->id,
            'reference'=>$this->reference,
            'user_id' => $this->user_id,
            'property' =>[
                    'id' => $this->property->id,
                    'name' => $this->property->name],
            'room' => [
                    'id' => $this->room->id,
                    'name' => $this->room->roomType->name,
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
                'discount_type'=>$this->offer->discount_type,
                'code'=>$this->offer->code ?? null
            ] : null,
            'original_price'=>app(CurrencyService::class)->convert($this->original_price,config('app.currency', 'USD'),$currency),
            'discount_amount'=>app(CurrencyService::class)->convert($this->discount_amount,config('app.currency', 'USD'),$currency),
            'total_price' => app(CurrencyService::class)->convert($this->total_price,config('app.currency', 'USD'),$currency),
            'currency' => $currency,
            'expires_at' => $this->status === BookingStatus::PENDING
                ? $this->expires_at?->toIso8601String()
                : null,
            'balance_due_date'=>$this->balance_due_date,
            'status' => $this->status,
            'cancellation_reason' => $this->status === BookingStatus::CANCELLED ? $this->cancellation_reason : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'invoice_number' => $this->invoice_number,
            'invoice_path' => $this->invoice_path ? asset('storage/' . $this->invoice_path) : null,
        ];
    }
}
