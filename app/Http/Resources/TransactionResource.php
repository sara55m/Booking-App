<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\CurrencyService;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //get user preferred currency
        $currency = strtoupper(
            auth()->user()?->currency ?? config('app.currency', 'USD')
        );

        $baseCurrency = strtoupper(config('app.currency', 'USD'));

        $currencyService = app(CurrencyService::class);
        return [
            'id'=>$this->id,

            'booking'=>[
                'id'=>$this->booking->id,
                'reference'=>$this->booking->reference
            ],
            'property'=>[
                'id'=>$this->booking->property->id,
                'name'=>$this->booking->property->name,
            ],
            'amount' => $currencyService->convert(
                $this->amount,
                $baseCurrency,
                $currency
            ),

            'remaining' => $currencyService->convert(
                $this->remaining,
                $baseCurrency,
                $currency
            ),

            'currency'=>$currency,

            'refunded_amount' => $currencyService->convert(
                $this->refunded_amount,
                $baseCurrency,
                $currency
            ),

            'status' => $this->status,

            'payment_method' => $this->payment_method,

            'transaction_id' => $this->transaction_id,

            'paid_at' => optional($this->paid_at)->format('Y-m-d H:i:s'),

            'refunded_at'=>optional($this->refunded_at)->format('Y-m-d H:i:s'),

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            ];
    }
}
