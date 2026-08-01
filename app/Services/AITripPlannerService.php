<?php

namespace App\Services;
use App\Models\City;
use App\Models\Country;
use App\Models\Property;
use Carbon\Carbon;
use App\Http\Resources\PropertyResource;
use Illuminate\Support\Collection;

class AITripPlannerService
{
    public function __construct(

        protected GroqService $groq,

        protected OfferService $offerService,
    ) {}

    public function extractTripDetails(string $message): array
    {
        //load available cities and countries form the database
        $countries = Country::orderBy('name')
        ->get(['name', 'iso_code'])
        ->map(fn ($country) => [
            'name' => $country->name,
            'iso_code' => $country->iso_code,
        ])
        ->values();

        $cities = City::orderBy('name')
            ->get(['name', 'slug'])
            ->map(fn ($city) => [
                'name' => $city->name,
                'slug' => $city->slug,
            ])
            ->values();

        //define allowed travel styles and interests

        $travelStyles = [
            'family',
            'romantic',
            'business',
            'adventure',
            'luxury',
            'budget',
            'solo',
        ];

        $interests = [
            'history',
            'culture',
            'food',
            'shopping',
            'nature',
            'beach',
            'nightlife',
            'museums',
            'kids',
            'adventure',
        ];

        //system propmpt

        $prompt = "
            You are an AI travel planning assistant.

            Your task is to extract trip planning information from the user's request.

            Return ONLY valid JSON.

            Do NOT include markdown.
            Do NOT include explanations.
            Do NOT wrap the JSON inside ```.

            Available countries:

            {$countries->toJson(JSON_PRETTY_PRINT)}

            Available cities:

            {$cities->toJson(JSON_PRETTY_PRINT)}

            Allowed travel styles:

            " . json_encode($travelStyles, JSON_PRETTY_PRINT) . "

            Allowed interests:

            " . json_encode($interests, JSON_PRETTY_PRINT) . "

            Rules:

            - Return the country ISO code.
            - Return the city slug.
            - Return ONLY travel styles from the allowed list.
            - Return ONLY interests from the allowed list.
            - Convert budget to a number only.
            - Convert trip duration into an integer number of days.
            - Dates must use YYYY-MM-DD.
            - If a value is not mentioned, return null.
            - If no interests are mentioned, return an empty array.
            - Never invent countries, cities, travel styles, or interests.

            Return exactly this JSON structure:

            {
                \"country\": null,
                \"city\": null,
                \"days\": null,
                \"budget\": null,
                \"travel_style\": null,
                \"interests\": [],
                \"start_date\": null,
                \"end_date\": null
            }

            User request:

            {$message}
            ";

        //talk to groq(send the system propmpt and the user message and get the filters)
        $content = $this->groq->chat(
            $prompt,
            $message
        );

        $trip = json_decode($content, true);

        if (! is_array($trip)) {
            throw new RuntimeException(
                'AI returned invalid JSON.'
            );
        }
        //filters array
        return $trip;
    }

    private function getNightsCount(array $trip): int
    {
        // If exact dates are available, use them.
        if (! empty($trip['start_date']) && ! empty($trip['end_date'])) {

            return max(
                1,
                Carbon::parse($trip['start_date'])
                    ->diffInDays($trip['end_date'])
            );
        }

        // Otherwise infer nights from trip duration.
        if (! empty($trip['days'])) {

            return max(1, $trip['days'] - 1);
        }

        // Default.
        return 1;
    }

    private function searchProperties(array $trip,int $nightsCount): Collection
    {

        return Property::query()
            ->where('is_active', true)
            ->withActiveOffer($nightsCount)
            ->city($trip['city'])
            ->country($trip['country'])
            ->withMin('roomTypes', 'base_price')
            ->with([
                'coverImage',
                'city.country',
                'amenities',
            ])
            ->take(10)
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
                'type'=>$property->propertyType->name,
                'original_price' => $pricing['originalPrice'],
                'final_price' => $pricing['finalPrice'],
                'currency' => 'EGP',
                'hotel_rating' => $property->rating,
                'guest_rating' => round($property->average_rating, 1),
                'description' => $property->description,
                'available_amenities' => $property->amenities
                    ->pluck('name')
                    ->toArray(),
            ];

        })->toArray();
    }

    private function searchTravelCategories(array $trip): Collection
    {
        if (empty($trip['city'])) {
            return collect();
        }

        return City::query()
        ->firstWhere('slug', $trip['city'])
        ?->travelCategories()
        ->active()
        ->get() ?? collect();
    }

    private function buildCategoryContext(Collection $categories): array
    {
        return $categories->map(function ($category) {

            return [
                'name' => $category->name,
                'description' => $category->description,
            ];

        })->toArray();
    }

    private function buildPlannerContext(
        array $trip,
        Collection $properties,
        Collection $categories,
        int $nightsCount
    ): array {



        return [

            'trip' => $trip,

            'properties' => $this->buildPropertyContext($properties,$nightsCount),

            'travel_categories' => $this->buildCategoryContext($categories),

        ];
    }
    private function getSystemPrompt(): string
    {
        $language = app()->getLocale() === 'ar'
        ? 'Arabic'
        : 'English';

        return <<<PROMPT
            You are an AI Travel Planner for a hotel booking platform.

            Always reply in {$language}.

            Your job is to create a personalized travel itinerary using ONLY the information provided in the prompt.

            =========================
            STRICT RULES
            =========================

            1. Use ONLY the hotel(s) provided.
            2. Use ONLY the travel categories provided.
            3. Never invent:
            - hotels
            - attractions
            - landmarks
            - restaurants
            - shopping malls
            - museums
            - beaches
            - parks
            - neighborhoods
            - transportation details
            - prices
            - hotel amenities
            4. If a hotel feature is not provided, do not mention it.
            5. Do not assume that a city has famous attractions.
            6. Do not mention any place by name unless it exists in the provided data.
            7. If there are no suitable hotels, politely explain that no matching hotels were found.
            8. Never mention unavailable information.
            9. Do not repeat the same activity every day.
            10. Keep the itinerary realistic and balanced.
            11.Do not invent natural landmarks. If the travel category is Nature, suggest generic outdoor activities without mentioning forests, mountains, lakes, rivers, beaches, or parks unless those are provided.
            12.Do not mention restaurants by name.
            13.Choose the hotel that best matches the user's preferences. Do not always choose the highest-rated hotel. Consider:
                budget
                travel style
                interests
                available offers
                guest rating
                amenities

            =========================
            HOTEL RECOMMENDATION
            =========================

            Recommend ONLY ONE hotel.

            Explain briefly why it is the best choice using ONLY:

            - guest rating
            - hotel rating
            - amenities
            - pricing
            - location
            - available offers

            Do not invent additional reasons.

            =========================
            TRAVEL CATEGORIES
            =========================

            Travel categories describe activity TYPES.

            Example mappings:

            Food
            → Try local cuisine.
            → Visit local food markets.
            → Enjoy traditional dishes.

            Shopping
            → Explore local markets.
            → Browse shopping districts.
            → Shop for souvenirs.

            Nature
            → Relax in green spaces.
            → Enjoy scenic walks.
            → Spend time outdoors.

            History
            → Discover the city's historical heritage.

            Culture
            → Experience local traditions.

            Nightlife
            → Enjoy the evening atmosphere.

            Beach
            → Relax by the sea.

            Museums
            → Visit museums.

            Kids
            → Enjoy family-friendly activities.

            Adventure
            → Enjoy outdoor adventure activities.

            Luxury
            → Enjoy premium experiences.

            Business
            → Leave free time for work or meetings.

            Never invent specific attraction names.

            =========================
            ITINERARY
            =========================

            Create one schedule for each day.

            Each day should include:

            Morning
            Afternoon
            Evening

            Keep activities varied.

            Do not recommend the hotel itself as an activity.

            Hotel amenities should ONLY justify the hotel recommendation, not fill the itinerary.

            =========================
            LANGUAGE
            =========================

            Respond in the same language as the user's request.

            Write naturally and professionally.

            Use Markdown headings and bullet points.

            =========================
            OUTPUT STRUCTURE
            =========================

            # Trip Summary

            - Destination
            - Duration
            - Budget
            - Travel Style if none is provided display Flexible
            - Interests

            # Recommended Hotel

            Hotel Name

            Why this hotel?

            - reason
            - reason
            - reason

            Hotel Class: ★★★
            Guest Rating: ⭐⭐⭐⭐⭐

            # Daily Itinerary

            ## Day 1

            ### Morning
            ...

            ### Afternoon
            ...

            ### Evening
            ...

            Repeat for all days.

            # Travel Tips

            Provide 3–5 short tips related ONLY to the destination and travel categories.

            =========================
            IMPORTANT
            =========================

            Everything must come ONLY from the supplied context.

            If something is unknown, omit it.

            Never hallucinate.
        PROMPT;
    }


    private function buildUserPrompt(array $context): string
    {
        $trip = json_encode($context['trip'], JSON_PRETTY_PRINT);

        $properties = json_encode(
            $context['properties'],
            JSON_PRETTY_PRINT
        );

        $categories = json_encode(
            $context['travel_categories'],
            JSON_PRETTY_PRINT);
        return <<<PROMPT
            Trip details:

            {$trip}

            Available properties:

            {$properties}

            Available travel categories:

            {$categories}

            Create a complete itinerary using ONLY this information.
            PROMPT;
    }

    private function generateTripPlan(
        array $trip,
        Collection $properties,
        Collection $travelCategories,
        int $nightsCount

    ): string {

        $systemPrompt = $this->getSystemPrompt();

        $context = $this->buildPlannerContext(
            $trip,
            $properties,
            $travelCategories,
            $nightsCount
        );

        $userPrompt = $this->buildUserPrompt($context);

        return $this->groq->chat(
            $systemPrompt,
            $userPrompt
        );
    }


    public function reply(string $message): array
    {
        // 1. Extract trip information
        $trip = $this->extractTripDetails($message);

        $nightsCount = $this->getNightsCount($trip);

        // 2. Search properties
        $properties = $this->searchProperties($trip,$nightsCount);

        // 3. Search travel categories
        $travelCategories = $this->searchTravelCategories($trip);

        // 4. No properties
        if ($properties->isEmpty()) {

            return [
                'assistant' => __('messages.ai.no_matching_properties'),
                'properties' => [],
            ];
        }

        // 5. Generate itinerary
        try {

            $plan = $this->generateTripPlan(
                $trip,
                $properties,
                $travelCategories,
                $nightsCount
            );


        } catch (\Throwable $e) {

            report($e);

            $plan = __('messages.ai.trip_planner_default');

        }

        // 6. Return response
        $properties->each(function ($property) use ($nightsCount) {
            $property->nights = $nightsCount;
        });

        return [

            'assistant' => $plan,

            'properties' => PropertyResource::collection($properties),

        ];
    }

    public function streamTripPlan(
    array $trip,
    Collection $properties,
    Collection $travelCategories,
    int $nightsCount,
    callable $onChunk
    ): void {

        $systemPrompt = $this->getSystemPrompt();

        $context = $this->buildPlannerContext(
            $trip,
            $properties,
            $travelCategories,
            $nightsCount
        );

        $userPrompt = $this->buildUserPrompt($context);

        $this->groq->streamChat(
            $systemPrompt,
            $userPrompt,
            $onChunk
        );
    }

    public function streamReply(string $message, callable $onChunk): array
    {
        // 1. Extract trip information
        $trip = $this->extractTripDetails($message);

        // 2. Calculate nights
        $nightsCount = $this->getNightsCount($trip);

        // 3. Search properties
        $properties = $this->searchProperties(
            $trip,
            $nightsCount
        );

        // 4. Search travel categories
        $travelCategories = $this->searchTravelCategories($trip);

        // 5. No properties
        if ($properties->isEmpty()) {

            $onChunk(
                __('messages.ai.no_matching_properties')
            );

            return [
                'properties' => [],
                'nights_count' => $nightsCount,
            ];
        }

        // 6. Stream AI itinerary
        try {

            $this->streamTripPlan(
                $trip,
                $properties,
                $travelCategories,
                $nightsCount,
                $onChunk
            );

        } catch (\Throwable $e) {

            report($e);

            $onChunk(
                __('messages.ai.trip_planner_default')
            );
        }

        // 7. Attach nights to properties
        $properties->each(function ($property) use ($nightsCount) {
            $property->nights = $nightsCount;
        });

        return [
            'properties' => $properties,
            'nights_count' => $nightsCount,
        ];
    }


}
