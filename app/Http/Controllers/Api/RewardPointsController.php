<?php

namespace App\Http\Controllers\Api;

use App\Enums\RewardPointType;
use App\Http\Controllers\Controller;
use App\Http\Resources\RewardPointResource;
use Illuminate\Http\Request;
use App\Services\RewardService;
use App\Models\RewardPoint;
use Illuminate\Validation\Rule;


class RewardPointsController extends Controller
{
    public function summary(Request $request,RewardService $rewardService){

        $data = $rewardService->getSummary($request->user());

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.reward_points_summary_retrieved_successfully'),
            'data'=>$data
        ]);
    }

    public function history(Request $request){

        $request->validate([
            'type'=>['nullable',Rule::in(RewardPointType::values())]
        ]);

        $query=RewardPoint::where('user_id',$request->user()->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $history=$query->latest()->paginate(10);

        return response()->json([
            'status_code'=>200,
            'message'=>__('messages.reward_points_history_retrieved_successfully'),
            'data'=>RewardPointResource::collection($history)
        ]);
    }

    public function calculate(Request $request,RewardService $rewardService){
        $request->validate([
            'amount'=>['required','numeric','min:1'],
            'points'=>['nullable','integer','min:0']
        ]);

        $result=$rewardService->calculate(
            $request->user(),
            $request->amount,
            $request->input('points',0));

        return response()->json([
            'status_code' => 200,
            'message' => __('messages.reward_discount_calculated_successfully'),
            'data' => $result,
        ]);

    }
}
