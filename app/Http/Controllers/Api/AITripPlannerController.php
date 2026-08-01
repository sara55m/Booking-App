<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Services\AITripPlannerService;
use App\Http\Requests\Properties\AISearchRequest;
use App\Http\Requests\TripPlans\StoreRequest;
use App\Models\AiConversation;
use App\Models\TripPlan;
use App\Http\Resources\TripPlanResource;

class AITripPlannerController extends Controller
{
    public function __construct(
        protected AITripPlannerService $plannerService
    ) {}

    public function tripPlanner(
        AISearchRequest $request,
    ) {
        $conversationId = $request->validated('conversation_id');
        return response()->json(
            $this->plannerService->reply(
                $request->validated('query'),
                $conversationId
            )
        );
    }

    public function tripPlannerStream(AISearchRequest $request)
    {
        $query = $request->validated('query');
        $conversationId = $request->validated('conversation_id');

        return response()->stream(function () use ($query, $conversationId) {

            $result = $this->plannerService->streamReply(
                $query,
                $conversationId,
                function (string $chunk) {

                    echo json_encode([
                        'type' => 'chunk',
                        'content' => $chunk,
                    ]) . "\n";

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }

                    flush();
                }
            );

            // Send properties after AI finishes
            echo json_encode([
                'type' => 'complete',
                'properties' => PropertyResource::collection(
                    $result['properties']
                )->resolve(),
                'nights_count' => $result['nights_count'],
            ]) . "\n";

            if (ob_get_level() > 0) {
                ob_flush();
            }

            flush();

        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=UTF-8',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        $conversation = AiConversation::where('id', $validated['conversation_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();


        if (empty($conversation->trip_context)) {
            return response()->json([
                'message' => __('messages.ai.no_trip_plan_found'),
            ], 422);
        }

        $lastPlan = $conversation->messages()
            ->where('role', 'assistant')
            ->latest()
            ->first();


        if (! $lastPlan) {
            return response()->json([
                'message' => __('messages.ai.no_generated_trip_plan_found'),
            ], 422);
        }

        $trip = $conversation->trip_context;

        $tripPlan = TripPlan::create([
            'user_id' => auth()->id(),

            'conversation_id' => $conversation->id,

            'title' => $validated['title'],

            'country' => $trip['country'] ?? null,

            'city' => $trip['city'] ?? null,

            'days' => $trip['days'] ?? null,

            'budget' => $trip['budget'] ?? null,

            'travel_style' => $trip['travel_style'] ?? null,

            'interests' => $trip['interests'] ?? [],

            'start_date' => $trip['start_date'] ?? null,

            'end_date' => $trip['end_date'] ?? null,

            'nights_count' => $conversation->nights_count,

            'plan' => $lastPlan->content,
        ]);

        return response()->json([
            'message' => __('messages.ai.trip_plan_saved_successfully'),
            'trip_plan' => new TripPlanResource($tripPlan),
        ], 201);
    }
}
