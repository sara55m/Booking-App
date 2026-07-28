<?php

namespace App\Services;

use App\Models\Amenity;
use App\Models\PropertyType;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use App\Models\City;
use App\Services\GroqService;

class AISearchService
{

    public function __construct(
        protected GroqService $groq
    ) {
    }

    public function extractFilters(string $query): array
    {
        $cities = City::orderBy('name')
            ->get(['name', 'slug'])
            ->map(fn ($city) => [
                'name' => $city->name,
                'slug' => $city->slug,
            ])
            ->values();

        $types = PropertyType::orderBy('name')
            ->get(['name', 'slug'])
            ->map(fn ($type) => [
                'name' => $type->name,
                'slug' => $type->slug,
            ])
            ->values();

        $amenities = Amenity::orderBy('name')
            ->pluck('name')
            ->values();

        $prompt = "
            You are an AI assistant for a hotel booking platform.

            Your task is to extract search filters from the user's request.

            Return ONLY valid JSON.
            Do NOT include markdown.
            Do NOT include explanations.
            Do NOT wrap the JSON inside ```.

            Available cities:
            {$cities->toJson(JSON_PRETTY_PRINT)}

            Available property types:
            {$types->toJson(JSON_PRETTY_PRINT)}

            Available amenities:
            {$amenities->toJson(JSON_PRETTY_PRINT)}

            Rules:

            - Return city SLUGS.
            - Return property type SLUGS.
            - Amenities must match exactly one of the available amenities.
            - If the user mentions multiple amenities, return all of them.
            - Convert prices to numbers.
            - Guests must be an integer.
            - Dates must use YYYY-MM-DD.
            - If a field is not mentioned, return null.

            Allowed sort values:
            price_asc
            price_desc
            hotel_rating_desc
            guest_rating_desc
            distance
            newest

            Return exactly this JSON structure:

            {
                \"country\":null,
                \"city\": null,
                \"type\": null,
                \"check_in\": null,
                \"check_out\": null,
                \"guests\": null,
                \"min_price\": null,
                \"max_price\": null,
                \"guest_rating\": null,
                \"hotel_rating\": null,
                \"amenities\": [],
                \"sort\": null
            }

            User request:

            {$query}
            ";

        $content = $this->groq->chat(
            $prompt,
            $query
        );

        $filters = json_decode($content, true);

        if (! is_array($filters)) {
            throw new RuntimeException('AI returned invalid JSON.');
        }

        return $filters;
    }

    public function normalizeFilters(array $filters): array
    {

        if (! empty($filters['amenities'])) {

            $amenityIds = Amenity::whereIn('name', $filters['amenities'])
            ->pluck('id')->toArray();

            $filters['amenities']=$amenityIds;
        }

        return $filters;
    }
}
