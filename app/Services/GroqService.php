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

    public function streamChat(
    string $systemPrompt,
    string $userPrompt,
    callable $onChunk
    ): void {
    $response = Http::withToken(config('services.groq.api_key'))
        ->baseUrl(config('services.groq.url'))
        ->withOptions([
            'stream' => true,
        ])
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
            'stream' => true,
        ]);

    $response->throw();

    $body = $response->toPsrResponse()->getBody();

    // Keep incomplete SSE data between reads.
    $buffer = '';

    while (! $body->eof()) {

        $buffer .= $body->read(1024);

        // Process only complete lines.
        while (($position = strpos($buffer, "\n")) !== false) {

            $line = substr($buffer, 0, $position);

            // Remove the processed line from the buffer.
            $buffer = substr($buffer, $position + 1);

            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (! str_starts_with($line, 'data:')) {
                continue;
            }

            $data = trim(substr($line, 5));

            if ($data === '[DONE]') {
                return;
            }

            $decoded = json_decode($data, true);

            if (! is_array($decoded)) {
                continue;
            }

            $content = $decoded['choices'][0]['delta']['content'] ?? null;

            if ($content !== null && $content !== '') {
                $onChunk($content);
            }
        }
    }
    }
}
