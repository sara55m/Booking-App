<?php

namespace App\Services;
use App\Models\City;
use App\Models\Country;
use App\Models\Property;
use Carbon\Carbon;
use App\Http\Resources\PropertyResource;
use Illuminate\Support\Collection;
use App\Models\AiConversation;

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

    private function formatTripContext(array $trip): string
    {
        return json_encode(
            $trip,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }

    private function updateTripContext(
        array $currentTrip,
        string $message
        ): array {

    $systemPrompt = <<<PROMPT
        You update an existing travel plan.

        Your job is to modify the existing trip details based ONLY on the user's latest message.

        Rules:
        - Keep every existing value unless the user explicitly changes it.
        - Never invent information.
        - If the user changes the budget, update only the budget.
        - If the user changes the destination, update the destination.
        - If the user changes the duration, update the duration.
        - If the user adds an interest, keep the existing interests and add the new one.
        - If the user removes an interest, remove only that interest.
        - Return ONLY a valid JSON object.
        - Do NOT use markdown.
        - Do NOT use ```json.
        - Do NOT add explanations.
        PROMPT;

        $userPrompt = <<<PROMPT
            Current trip:

            {$this->formatTripContext($currentTrip)}

            User's follow-up message:

            {$message}

            Return the complete updated trip using exactly this structure:

            {
                "country": null,
                "city": null,
                "days": null,
                "budget": null,
                "travel_style": null,
                "interests": [],
                "start_date": null,
                "end_date": null
            }
            PROMPT;

            $content = $this->groq->chat(
                $systemPrompt,
                $userPrompt
            );

            // Remove possible markdown fences if the model still adds them
            $content = trim($content);

            $content = preg_replace(
                '/^```json\s*|\s*```$/i',
                '',
                $content
            );

            $updatedTrip = json_decode($content, true);

            if (! is_array($updatedTrip)) {
                throw new \RuntimeException(
                    'AI returned invalid trip context: ' . $content
                );
            }

        return $updatedTrip;
    }

    private function generateTripPlan(
        array $trip,
        Collection $properties,
        Collection $travelCategories,
        int $nightsCount,
        AiConversation $conversation

    ): string {
        $systemPrompt = $this->getSystemPrompt();

        $context = $this->buildPlannerContext(
            $trip,
            $properties,
            $travelCategories,
            $nightsCount
        );

        $userPrompt = $this->buildUserPrompt($context);

        //get the conversation messages
        $messages = $conversation->messages()
        ->latest()
        ->take(10)
        ->get()
        ->reverse()
        ->map(function ($message) {
            return [
                'role' => $message->role,
                'content' => $message->content,
            ];
        })
        ->values()
        ->toArray();

        $messages[] = [
            'role' => 'user',
            'content' => $userPrompt,
        ];

        return $this->groq->chatWithHistory(
            $systemPrompt,
            $messages
        );
    }


    public function reply(string $message,?int $conversationId = null): array
    {
        // 1. Get existing conversation or create a new one
        if ($conversationId) {

            $conversation = AiConversation::where('id', $conversationId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

        } else {

            $conversation = AiConversation::create([
                'user_id' => auth()->id(),
            ]);
        }

        // 3. Save user's message
        $conversation->messages()->create([
            'role' => 'user',
            'content' => $message,
        ]);

        // 4. Extract trip information
        //if the conversation has trip context, update it
        if ($conversation->trip_context) {

            $trip = $this->updateTripContext(
                $conversation->trip_context,
                $message
            );
        //otherwise extract the trip information
        } else {

            $trip = $this->extractTripDetails(
                $message
            );
        }

        // 5. Save trip information to conversation
        $conversation->update([
            'trip_context' => $trip,
        ]);

        // 6. calculate the nights count
        $nightsCount = $this->getNightsCount($trip);

        // 7. Search properties
        $properties = $this->searchProperties($trip,$nightsCount);

        // 8. Search travel categories
        $travelCategories = $this->searchTravelCategories($trip);

        // 9. No properties
        if ($properties->isEmpty()) {

            return [
                'conversation_id' => $conversation->id,
                'assistant' => __('messages.ai.no_matching_properties'),
                'properties' => [],
            ];
        }

        // 10. Generate itinerary
        try {

            $plan = $this->generateTripPlan(
                $trip,
                $properties,
                $travelCategories,
                $nightsCount,
                $conversation
            );

            // 11. Save itinerary to conversation
            $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $plan,
            ]);


        } catch (\Throwable $e) {

            report($e);

            $plan = __('messages.ai.trip_planner_default');

        }

        // 12. Return response
        $properties->each(function ($property) use ($nightsCount) {
            $property->nights = $nightsCount;
        });

        return [
            'conversation_id' => $conversation->id,

            'assistant' => $plan,

            'properties' => PropertyResource::collection($properties),

        ];
    }

    public function streamTripPlan(
    array $trip,
    Collection $properties,
    Collection $travelCategories,
    int $nightsCount,
    callable $onChunk,
    AiConversation $conversation
    ): void {

        $systemPrompt = $this->getSystemPrompt();

        $context = $this->buildPlannerContext(
            $trip,
            $properties,
            $travelCategories,
            $nightsCount
        );

        $userPrompt = $this->buildUserPrompt($context);

        //get the conversation messages
        $messages = $conversation->messages()
        ->latest()
        ->take(10)
        ->get()
        ->reverse()
        ->map(function ($message) {
            return [
                'role' => $message->role,
                'content' => $message->content,
            ];
        })
        ->values()
        ->toArray();

        $messages[] = [
            'role' => 'user',
            'content' => $userPrompt,
        ];

        $this->groq->streamChat(
            $systemPrompt,
            $messages,
            $onChunk
        );
    }

    public function streamReply(string $message,?int $conversationId = null,callable $onChunk): array
    {
        // 1. Get existing conversation or create a new one
        if ($conversationId) {

        $conversation = AiConversation::where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        } else {

            $conversation = AiConversation::create([
                'user_id' => auth()->id(),
            ]);
        }
        // 3. Save user's message
        $conversation->messages()->create([
            'role' => 'user',
            'content' => $message,
        ]);

        // 4. Extract trip information
        //if the conversation has trip context, update it
        if ($conversation->trip_context) {

            $trip = $this->updateTripContext(
                $conversation->trip_context,
                $message
            );
        //otherwise extract the trip information
        } else {

            $trip = $this->extractTripDetails(
                $message
            );
        }

        // 5. Save trip information to conversation
        $conversation->update([
            'trip_context' => $trip,
        ]);

        // 6. Calculate nights
        $nightsCount = $this->getNightsCount($trip);

        // 7. Search properties
        $properties = $this->searchProperties(
            $trip,
            $nightsCount
        );

        // 8. Search travel categories
        $travelCategories = $this->searchTravelCategories($trip);

        // 9. No properties
        if ($properties->isEmpty()) {

            $onChunk(
                __('messages.ai.no_matching_properties')
            );

            return [
                'conversation_id' => $conversation->id,
                'properties' => [],
                'nights_count' => $nightsCount,
            ];
        }

        $assistantResponse = '';

        // 10. Stream AI itinerary
        try {

        $this->streamTripPlan(
            $trip,
            $properties,
            $travelCategories,
            $nightsCount,
            function (string $chunk) use (
                &$assistantResponse,
                $onChunk
            ) {

                // Keep complete response for database
                $assistantResponse .= $chunk;

                // Send chunk to controller
                $onChunk($chunk);
            },
            $conversation
        );


        } catch (\Throwable $e) {

            report($e);

            $assistantResponse = __('messages.ai.trip_planner_default');

            $onChunk($assistantResponse);
        }

        // 11. Save complete assistant response
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $assistantResponse,
        ]);

        // 7. Attach nights to properties
        $properties->each(function ($property) use ($nightsCount) {
            $property->nights = $nightsCount;
        });

        return [
            'conversation_id' => $conversation->id,
            'properties' => $properties,
            'nights_count' => $nightsCount,
        ];
    }


}
