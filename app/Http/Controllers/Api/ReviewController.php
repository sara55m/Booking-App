<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Reviews\StoreRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Models\Property;

class ReviewController extends Controller
{

    public function index(Property $property)
    {
        $reviews = $property->approvedReviews()->with('user','tags','booking','property')->latest()->paginate(10);
        return response()->json(
            [
                'status_code' => 200,
                'message' => __('messages.reviews_retrieved_successfully'),
                'data' => ReviewResource::collection($reviews)],200);
    }

    public function store(StoreRequest $request)
    {
        $booking = Booking::where('reference', $request->booking_reference)->firstOrFail();
        //validate the user already has a booking to review
        Gate::authorize('create', [Review::class,$booking]);

        //ensure booking status is completed
        if($booking->status !== BookingStatus::COMPLETED){
            return response()->json(['message'=>__("messages.you_can_only_review_completed_bookings")], 403);
        }

        //ensure user has not already reviewed this booking
        if(Review::where('booking_id', $booking->id)->where('user_id',auth()->id())->exists()){
            return response()->json(['message'=>__("messages.you_have_already_reviewed_this_booking")], 403);
        }

        $review=DB::transaction(function () use ($request, $booking) {
            $review=Review::create([
                'user_id'=>auth()->id(),
                'property_id'=>$booking->property_id,
                'booking_id'=>$booking->id,
                'rating'=>$request->rating,
                'comment'=>$request->comment,
            ]);

            //attach tags if provided
            if($request->has('review_tags')){
                $review->tags()->sync($request->review_tags ?? []);
            }

            $review->load('user','tags','property','booking');

            return $review;
        });

        return response()->json(
            [
                'status_code' => 201,
                'message' => __('messages.review_added_successfully'),
                'data' => new ReviewResource($review)],201);

    }

    public function show(Review $review)
    {
        Gate::authorize('view', $review);

        $review->load('user','tags','property','booking');
        return response()->json(
            [
                'status_code' => 200,
                'message' => __('messages.review_retrieved_successfully'),
                'data' => new ReviewResource($review)],200);
    }

    public function update(Request $request, Review $review)
    {
        Gate::authorize('update', $review);

        DB::transaction(function () use ($request, $review) {
            $review->update($request->only('rating', 'comment'));

            //update tags if provided
            if($request->has('review_tags')){
                $review->tags()->sync($request->review_tags ?? []);
            }
        });

        $review->load('user','tags','booking','property');

        return response()->json(
            [
                'status_code' => 200,
                'message' => __('messages.review_updated_successfully'),
                'data' => new ReviewResource($review)],200);
    }

    public function destroy(Review $review)
    {
        Gate::authorize('delete', $review);
        //delete the review and detach tags
        $review->tags()->detach();
        $review->delete();
        return response()->json(
            [
                'status_code' => 200,
                'message' => __('messages.review_deleted_successfully'),
            ],200);
    }
}
