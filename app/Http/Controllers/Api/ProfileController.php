<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Http\Requests\Profile\UpdateRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Profile\DeleteRequest;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\PropertyResource;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Http\Resources\PaymentMethodResource;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\Stripe;
use Illuminate\Validation\Rule;
use App\Enums\PaymentStatus;
use App\Http\Resources\TransactionResource;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function show(Request $request){
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.user_profile_retrieved_successfully'),
            'data' =>UserResource::make($user)
        ]);
    }

    public function update(UpdateRequest $request){
        $user = $request->user();

        $oldImage = $user->image;

        DB::transaction(function () use ($request, $user, $oldImage) {

            $user->update($request->safe()->only(['name', 'email', 'phone']));

            // Handle image upload if provided
            if($request->hasFile('image')){
                $image=$request->file('image');
                //store the image in public storage
                $newImage=$image->store('profile_images', 'public');
                $user->update([
                    'image' => $newImage
                ]);
                //delete the old image if exists after the transaction is committed
                DB::afterCommit(function () use ($oldImage) {
                    if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                        Storage::disk('public')->delete($oldImage);
                    }
                });
            }

        });

        return response()->json([
            'status' => 'success',
            'message' => __('messages.user_profile_updated_successfully'),
            'data' => UserResource::make($user->fresh())
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request){

        DB::transaction(function () use ($request) {

            $user=$request->user();

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            // Keep the current device logged in, log out all others
            $currentToken = $user->currentAccessToken();

            if ($currentToken) {
                $user->tokens()
                    ->where('id', '!=', $currentToken->id)
                    ->delete();
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => __('messages.password_updated_successfully'),
        ]);
    }

    public function deleteImage(){
        $user = auth()->user();

        if (! $user->image) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.profile_image_not_found'),
            ], 404);
        }
        $oldImage = $user->image;

        DB::transaction(function () use ($user, $oldImage) {

            $user->update([
                'image' => null,
            ]);

            DB::afterCommit(function () use ($oldImage) {
                if($oldImage && Storage::disk('public')->exists($oldImage)){
                    Storage::disk('public')->delete($oldImage);
                }
            });
        });

        return response()->json([
            'status' => 'success',
            'message' => __('messages.user_profile_image_deleted_successfully'),
            'data' => UserResource::make($user->fresh())
        ]);
    }

    public function destroy(DeleteRequest $request){
        $user=$request->user();
        $oldImage=$user->image;

        DB::transaction(function () use ($user, $oldImage) {

            // Delete all API tokens (Sanctum)
            $user->tokens()->delete();

            // Delete the user
            $user->delete();

            // Delete profile image after successful commit
            DB::afterCommit(function () use ($oldImage) {
                if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                    Storage::disk('public')->delete($oldImage);
                }
            });
        });

        return response()->json([
            'status' => 'success',
            'message' => __('messages.account_deleted_successfully'),
        ]);
    }

    public function reviews(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $user=$request->user();

        $reviews = $user->approvedReviews()->with('tags','booking','property')
        ->when($request->filled('search'), function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $query->whereHas('tags', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%');
                })
                ->orWhereHas('property', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%');
                });
            });
        })
        ->latest()->paginate(10);
        return response()->json(
            [
                'status_code' => 200,
                'message' => $reviews->isEmpty()
                ? __('messages.no_reviews_yet')
                :__('messages.reviews_retrieved_successfully'),
                'data' => ReviewResource::collection($reviews),
                'pagination' => [

                    'current_page' => $reviews->currentPage(),

                    'last_page' => $reviews->lastPage(),

                    'per_page' => $reviews->perPage(),

                    'total' => $reviews->total(),
                ],
            ],200);
            
    }

    public function favorites(Request $request){
        $request->validate([
            'city' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable','exists:property_types,name'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $user=$request->user();

        $favorites=$user->favoriteProperties()
        ->where('is_active', true)
        ->when($request->filled('city'), function ($query) use ($request) {
            $query->city($request->city);
        })
        ->when($request->filled('type'), function ($query) use ($request) {
            $query->type($request->type);
        })
        ->when($request->filled('search'), function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->search . '%');
        })
        ->withActiveOffer()
        ->with('coverImage','city')
        ->withMin('rooms', 'price-per-night')
        ->latest()->paginate(10);

        return response()->json(
            [
                'status_code' => 200,
                'message' => $favorites->isEmpty()
                ? __('messages.no_favorite_properties_added_yet')
                :__('messages.favorites_retrieved_successfully'),
                'data' => PropertyResource::collection($favorites),
                'pagination' => [

                    'current_page' => $favorites->currentPage(),

                    'last_page' => $favorites->lastPage(),

                    'per_page' => $favorites->perPage(),

                    'total' => $favorites->total(),
                ],
            ],200);
    }

    public function paymentMethods(Request $request){
        $user =$request->user();

        $paymentMethods=$user->paymentMethods()->latest()->paginate(10);

        return response()->json([
            'status_code' => 200,
            'message' => $paymentMethods->isEmpty()
                ? __('messages.no_payment_methods_found')
                : __('messages.payment_methods_retrieved_successfully'),
    
            'data' => PaymentMethodResource::collection($paymentMethods),
    
            'pagination' => [
                'current_page' => $paymentMethods->currentPage(),
                'last_page' => $paymentMethods->lastPage(),
                'per_page' => $paymentMethods->perPage(),
                'total' => $paymentMethods->total(),
            ],
        ]);
    }

    public function setDefaultPaymentMethod(PaymentMethod $paymentMethod)
    {
        $user = auth()->user();

        $paymentMethod=$user->paymentMethods()->find($paymentMethod->id);

        if (! $paymentMethod) {
            return response()->json([
                'status_code' => 403,
                'message' => __('messages.unauthorized_action'),
            ], 403);
        }
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            DB::transaction(function () use ($user, $paymentMethod) {
    
                $user->paymentMethods()->update([
                    'is_default' => false,
                ]);
    
                $paymentMethod->update([
                    'is_default' => true,
                ]);

                if ($user->stripe_customer_id) {
                    \Stripe\Customer::update(
                        $user->stripe_customer_id,
                        [
                            'invoice_settings' => [
                                'default_payment_method' => $paymentMethod->stripe_payment_method_id,
                            ],
                        ]
                    );
                }
            });
    
            return response()->json([
                'status_code' => 200,
                'message' => __('messages.default_payment_method_updated_successfully'),
                'data' => new PaymentMethodResource($paymentMethod->fresh()),
            ]);
    
        } catch (\Throwable $e) {
    
            Log::error('Failed to set default payment method.', [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethod->id,
                'error' => $e->getMessage(),
            ]);
    
            return response()->json([
                'status_code' => 500,
                'message' => __('messages.something_went_wrong'),
            ], 500);
        }
    }

    public function deletePaymentMethod(PaymentMethod $paymentMethod)
    {
        $user = auth()->user();

        $paymentMethod=$user->paymentMethods()->find($paymentMethod->id);

        if (! $paymentMethod) {
            return response()->json([
                'status_code' => 403,
                'message' => __('messages.unauthorized_action'),
            ], 403);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {

            DB::transaction(function () use ($user, $paymentMethod) {
                //check if payment method is default
                $wasDefault = $paymentMethod->is_default;

                // Detach the payment method from Stripe
                $stripePaymentMethod = StripePaymentMethod::retrieve(
                    $paymentMethod->stripe_payment_method_id
                );

                $stripePaymentMethod->detach();
                
                $paymentMethod->delete();

                // If the deleted card was the default, promote another one
                if ($wasDefault) {

                    $newDefault = $user->paymentMethods()
                        ->latest()
                        ->first();

                    if ($newDefault) {

                        $newDefault->update([
                            'is_default' => true,
                        ]);

                        if ($user->stripe_customer_id) {
                            \Stripe\Customer::update(
                                $user->stripe_customer_id,
                                [
                                    'invoice_settings' => [
                                        'default_payment_method' => $newDefault->stripe_payment_method_id,
                                    ],
                                ]
                            );
                        }

                    } elseif ($user->stripe_customer_id) {

                        // User has no remaining payment methods
                        \Stripe\Customer::update(
                            $user->stripe_customer_id,
                            [
                                'invoice_settings' => [
                                    'default_payment_method' => null,
                                ],
                            ]
                        );
                    }
                }
            });

            return response()->json([
                'status_code' => 200,
                'message' => __('messages.payment_method_deleted_successfully'),
            ]);

        } catch (\Throwable $e) {

            Log::error('Failed to delete payment method.', [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethod->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status_code' => 500,
                'message' => __('messages.something_went_wrong'),
            ], 500);
        }
    }

    public function transactions(Request $request){

        $request->validate([
            'status' => ['nullable', Rule::in(PaymentStatus::values())],
            'booking' => ['nullable', 'integer', 'exists:bookings,id'],
            'property' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $user=$request->user();

        $transactions=Payment::query()
        ->whereHas('booking',function($query) use ($user){
            $query->where('user_id',$user->id);
        })
        ->when($request->filled('status'), function ($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->when($request->filled('booking'), function ($query) use ($request) {
            $query->where('booking_id', $request->booking);
        })
        ->when($request->filled('property'),function ($query) use ($request){
            $query->whereHas('booking.property',function ($query) use ($request){
                $query->where('name','like',"%$request->property%");
            });
        })
        ->when($request->filled('from'), function ($query) use ($request) {
            $query->whereDate('created_at', '>=', $request->from);
        })

        ->when($request->filled('to'), function ($query) use ($request) {
            $query->whereDate('created_at', '<=', $request->to);
        })
        ->with('booking.property')
        ->latest()

        ->paginate(10);

        return response()->json([
            'status_code' => 200,
            'message' => $transactions->isEmpty()
                ? __('messages.no_transactions_yet')
                : __('messages.transactions_retrieved_successfully'),
    
            'data' => TransactionResource::collection($transactions),
    
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);

    }
}
