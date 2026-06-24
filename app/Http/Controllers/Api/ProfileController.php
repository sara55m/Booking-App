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
}
