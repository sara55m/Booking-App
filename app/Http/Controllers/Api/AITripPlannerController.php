<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AITripPlannerService;
use App\Http\Requests\Properties\AISearchRequest;

class AITripPlannerController extends Controller
{
    public function __invoke(
        AISearchRequest $request,
        AITripPlannerService $plannerService
    ) {
        return response()->json(
            $plannerService->reply(
                $request->validated('query')
            )
        );
    }
}
