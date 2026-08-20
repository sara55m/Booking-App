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

    public function chatJson(string $systemPrompt, string $userPrompt): array
    {
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

                'response_format' => [
                    'type' => 'json_object',
                ],
            ])
            ->throw()
            ->json();

        return json_decode(
            $response['choices'][0]['message']['content'],
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    public function chatWithHistory(
    string $systemPrompt,
    array $messages
    ): string {
        $response = Http::withToken(config('services.groq.api_key'))
            ->baseUrl(config('services.groq.url'))
            ->post('/chat/completions', [

                'model' => config('services.groq.model'),

                'messages' => array_merge(
                    [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt,
                        ],
                    ],
                    $messages
                ),

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
    array $messages,
    callable $onChunk
    ): void {
        $response = Http::withToken(config('services.groq.api_key'))
            ->baseUrl(config('services.groq.url'))
            ->withOptions([
                'stream' => true,
            ])
            ->post('/chat/completions', [

                'model' => config('services.groq.model'),

                'messages' => array_merge(
                    [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt,
                        ],
                    ],
                    $messages
                ),

                'temperature' => 0.3,
                'stream' => true,
            ]);

        $response->throw();

        $body = $response->toPsrResponse()->getBody();

        $buffer = '';

        while (! $body->eof()) {

            $buffer .= $body->read(1024);

            while (($position = strpos($buffer, "\n")) !== false) {

                $line = substr($buffer, 0, $position);

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
