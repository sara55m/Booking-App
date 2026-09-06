<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    public function convert(
        float $amount,
        string $from,
        string $to
    ): float {
        if (strtoupper($from) === strtoupper($to)) {
            return round($amount, 2);
        }

        $rate = $this->getRate($from, $to);

        return round($amount * $rate, 2);
    }

    public function getRate(string $from, string $to): float
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return 1.0;
        }

        return Cache::remember(
            "exchange_rate:{$from}:{$to}",
            now()->addHours(12),
            function () use ($from, $to) {

                $response = Http::timeout(5)
                    ->get(
                        'https://v6.exchangerate-api.com/v6/'
                        . config('services.exchange_rate.key')
                        . "/pair/{$from}/{$to}"
                    );

                if ($response->failed()) {
                    throw new RuntimeException(
                        'Unable to retrieve exchange rate.'
                    );
                }

                $data = $response->json();

                if (($data['result'] ?? null) !== 'success') {
                    throw new RuntimeException(
                        'Currency conversion failed: '
                        . ($data['error-type'] ?? 'unknown error')
                    );
                }

                return (float) $data['conversion_rate'];
            }
        );
    }
}
