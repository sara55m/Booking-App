<?php

namespace App\Services;
use App\Models\Property;
use Carbon\Carbon;
use App\Http\Resources\PropertyResource;
use Illuminate\Support\Collection;

class AITravelAssistantService
{
    public function __construct(

        protected AISearchService $aiSearchService,

        protected GroqService $groq,

        protected OfferService $offerService,

    ) {}

    private function getNightsCount(array $filters): int
    {
        // If exact dates are available, use them.
        if (! empty($filters['check_in']) && ! empty($filters['check_out'])) {

            return max(
                1,
                Carbon::parse($filters['check_in'])
                    ->diffInDays($filters['check_out'])
            );
        }

        // Default.
        return 1;
    }

    private function searchProperties(array $filters,int $nightsCount): Collection
    {
        return Property::query()
        ->where('is_active', true)
        ->withMin('roomTypes', 'base_price')
        ->filter($filters)
        ->withActiveOffer($nightsCount)
        ->with([
            'coverImage',
            'city.country',
            'amenities',
        ])
        ->take(5)
        ->get();
    }

    private function buildPropertyContext(Collection $properties,int $nightsCount): array
    {
        return $properties->map(function ($property) use ($nightsCount) {

            $pricing = $this->offerService->calculatePrice(
                $property,
                $nightsCount
            );

            return [
                'name' => $property->name,
                'city' => $property->city->name,
                'country' => $property->city->country->name,
                'guest_rating' => round($property->average_rating, 1),
                'hotel_rating' => $property->star_rating,
                'image' => $property->coverImage ? asset('storage/'.$property->coverImage->image) : null,
                'description' => $property->description,
                'original_price' => $pricing['originalPrice'],
                'final_price' => $pricing['finalPrice'],
                'amenities' => $property->amenities
                    ->pluck('name')
                    ->values()
                    ->toArray(),
            ];

        })->toArray();
    }

    private function getSystemPrompt(): string
    {
        $language = app()->getLocale() === 'ar'
            ? 'Arabic'
            : 'English';

        return <<<PROMPT
        You are an AI travel assistant for a hotel booking platform.

        Always reply in {$language}.
        Your job is to help users choose the best hotel from the provided search results.

        IMPORTANT RULES:

        - Use ONLY the hotels provided in the context.
        - Never invent hotels, prices, ratings, amenities, locations, or descriptions.
        - Never recommend hotels that are not included in the provided list.
        - If no hotels are provided, politely explain that no matching properties were found and suggest relaxing one or two search filters.
        - Recommend at most three hotels.
        - Rank recommendations from best match to least suitable.
        - Base your recommendations on the user's preferences such as:
        - destination
        - budget
        - travel dates
        - number of guests
        - hotel rating
        - guest rating
        - amenities
        - property type
        - Mention only information that exists in the provided data.
        - Do not mention internal IDs or technical field names.
        - Do not use Markdown tables.
        - Keep the response concise, friendly, and natural.
        - Never translate hotel names.
        - Translate only your explanation to the user's language.
        - Never translate hotel names.
        - Always use the official hotel name exactly as provided in the property data.
        - Translate only your explanation to the user's language.

        For each recommended hotel:
        - Mention its name.
        - Explain in one or two sentences why it matches the user's request.
        - Mention relevant advantages such as price, rating, amenities, or location only if they are available in the provided data.

        PROMPT;
    }

    private function buildUserPrompt(
        string $message,
        array $context
    ): string {

        $properties = json_encode($context, JSON_PRETTY_PRINT);

        return <<<PROMPT
            User request:

            {$message}

            Matching properties:

            {$properties}

            1. Recommend the best matching hotels.
            2. Explain why each recommendation matches the user's request.
            3. If none are suitable, explain why and suggest relaxing some filters.

            PROMPT;
    }

    private function generateRecommendation(
        string $message,
        Collection $properties,
        int $nightsCount
    ): string {

        $systemPrompt = $this->getSystemPrompt();

        $context = $this->buildPropertyContext(
            $properties,
            $nightsCount
        );

        $userPrompt = $this->buildUserPrompt($message,$context);

        return $this->groq->chat(
            $systemPrompt,
            $userPrompt
        );
    }

    public function reply(string $message): array
    {
        // Extract filters using AI
        $filters = $this->aiSearchService->extractFilters($message);

        // Normalize filters
        $filters = $this->aiSearchService->normalizeFilters($filters);

        $nightsCount = $this->getNightsCount($filters);

        //search properties
        $properties=$this->searchProperties($filters,$nightsCount);

        if ($properties->isEmpty()) {
            return [
                'assistant' => __('messages.ai.no_matching_properties'),
                'properties' =>[],
            ];
        }

        // 3. Generate AI explanation
        try {
            $recommendation = $this->generateRecommendation(
                $message,
                $properties,
                $nightsCount
            );
        } catch (\Throwable $e) {
            report($e);

            $recommendation = __('messages.ai.default_recommendation');
        }

        $properties->each(function ($property) use ($nightsCount) {
            $property->nights = $nightsCount;
        });

        // 4. Return response
        return [
            'assistant' => $recommendation,
            'properties' => PropertyResource::collection($properties),
        ];
    }
}
