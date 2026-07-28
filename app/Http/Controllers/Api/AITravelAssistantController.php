<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Properties\AITravelAssistantRequest;
use App\Services\AITravelAssistantService;

class AITravelAssistantController extends Controller
{
    public function __invoke(
        AITravelAssistantRequest $request,
        AITravelAssistantService $assistant
    ) {
        return response()->json(
            $assistant->reply(
                $request->validated('message')
            )
        );
    }
}
