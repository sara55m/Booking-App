<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reviews\StoreRequest;
use App\Http\Requests\Reviews\UpdateRequest;
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
        $reviews = $property->approvedReviews()->with('user','categories','tags','booking','property')->latest()->paginate(10);
        return response()->json(
            [
                'status_code' => 200,
                'message' => __('messages.reviews_retrieved_successfully'),
                'data' => ReviewResource::collection($reviews)],200);
    }

    public function store(StoreRequest $request)
    {
        $validated=$request->validated();

        $booking = Booking::where('reference', $validated['booking_reference'])->firstOrFail();
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

        $review=DB::transaction(function () use ($validated, $booking) {
            $review=Review::create([
                'user_id'=>auth()->id(),
                'property_id'=>$booking->property_id,
                'booking_id'=>$booking->id,
                'rating'=>$validated['rating'],
                'comment'=>$validated['comment'] ?? null,
            ]);

            //attach review categories
            foreach ($validated['review_categories'] as $category) {

                $review->categories()->attach(
                    $category['category_id'],
                    [
                        'rating' => $category['rating'],
                    ]
                );
            }

            // Attach tags if provided
            if (array_key_exists('review_tags', $validated)) {
                $review->tags()->sync($validated['review_tags']);
            }

            $review->load(['user','tags','categories','property','booking']);

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

        $review->load('user','tags','categories','property','booking');
        return response()->json(
            [
                'status_code' => 200,
                'message' => __('messages.review_retrieved_successfully'),
                'data' => new ReviewResource($review)],200);
    }

    public function update(UpdateRequest $request, Review $review)
    {
        $validated=$request->validated();

        Gate::authorize('update', $review);

        //if review status is rejected -> can not edit
        if($review->status===ReviewStatus::Rejected){
            return response()->json(['message'=>__("messages.you_can_not_edit_rejected_reviews")], 403);
        }

        //if review status is approved the user can edit the review only within 24 hours
        if($review->status===ReviewStatus::Approved){
            $threshold = $review->approved_at->copy()->addHours(24);

            if(now()->greaterThan($threshold)){
                return response()->json(['message'=>__("messages.you_can_only_edit_reviews_within_24_hours_from_approval")], 403);
            }
        }

        //update pending and approved reviews
        DB::transaction(function () use ($validated, $review) {
            $reviewData=[];

            if(array_key_exists('rating',$validated)){
                $reviewData['rating']=$validated['rating'];
            }

            if(array_key_exists('comment',$validated)){
                $reviewData['comment']=$validated['comment'];
            }

            //check if review content was actually changed (rating or comment)
            $reviewChanged=
            (
            array_key_exists('rating',$reviewData) && $reviewData['rating'] != $review->rating)
            ||
            (
                array_key_exists('comment',$reviewData) && $reviewData['comment'] != $review->comment
            );

            // Update category ratings only if provided
            $categoriesChanged = false;
            if (array_key_exists('review_categories', $validated)) {
                $categoryRatings = collect($validated['review_categories'])
                    ->mapWithKeys(function ($category) {
                        return [
                            $category['category_id'] => [
                                'rating' => $category['rating'],
                            ],
                        ];
                    })
                    ->toArray();

                $changes=$review->categories()->sync($categoryRatings);

                $categoriesChanged =
                ! empty($changes['attached']) ||
                ! empty($changes['detached']) ||
                ! empty($changes['updated']);
            }

            //update tags if provided
            $tagsChanged = false;
            if (array_key_exists('review_tags', $validated)) {
                $changes=$review->tags()->sync($validated['review_tags']);

                $tagsChanged =
                ! empty($changes['attached']) ||
                ! empty($changes['detached']) ||
                ! empty($changes['updated']);
            }

            $hasChanges =
            $reviewChanged
            || $categoriesChanged
            || $tagsChanged;

            //if an approved review is edited change the status back to pending for review and moderation
            if (
                $review->status === ReviewStatus::Approved
                && $hasChanges
            ) {
                $reviewData['status'] = ReviewStatus::Pending;
            }

            //update review
            if (! empty($reviewData)) {
                $review->update($reviewData);
            }

        });

        $review->load(['user','tags','categories','booking','property']);

        return response()->json(
            [
                'status_code' => 200,
                'message' => __('messages.review_updated_successfully'),
                'data' => new ReviewResource($review)],200);
    }

    public function destroy(Review $review)
    {
        Gate::authorize('delete', $review);
        //detach categories
        $review->categories()->detach();
        //detach tags
        $review->tags()->detach();
        //delete the review
        $review->delete();
        return response()->json(
            [
                'status_code' => 200,
                'message' => __('messages.review_deleted_successfully'),
            ],200);
    }
}
