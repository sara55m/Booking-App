<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Services\AITripPlannerService;
use App\Http\Requests\Properties\AISearchRequest;

class AITripPlannerController extends Controller
{
    public function __construct(
        protected AITripPlannerService $plannerService
    ) {}

    public function tripPlanner(
        AISearchRequest $request,
    ) {
        return response()->json(
            $this->plannerService->reply(
                $request->validated('query')
            )
        );
    }

    public function tripPlannerStream(AISearchRequest $request)
    {
        $query = $request->validated('query');

        return response()->stream(function () use ($query) {

            $result = $this->plannerService->streamReply(
                $query,
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
}
