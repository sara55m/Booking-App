<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GroqService
{
    public function chat(string $systemPrompt, string $userPrompt): string
    {
        //this is where we actually talk with the ai model (groq)

        $response = Http::withToken(config('services.groq.api_key'))
            ->baseUrl(config('services.groq.url'))
            ->post('/chat/completions', [

                'model' => config('services.groq.model'),

                'messages' => [

                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],

                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ],
                ],

                'temperature' => 0.3,
            ])
            ->throw()
            ->json();

        return trim(
            $response['choices'][0]['message']['content']
        );
    }
}
