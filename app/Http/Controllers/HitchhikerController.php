<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Hitchhiker;
use App\Models\Rating;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HitchhikerController extends Controller
{
    public function getHitchhiker()
    {
        $hitchhikers = Hitchhiker::with([
            'user' => function ($query) {
                $query->select('id', 'phone_number');
            }
        ])->get();

        return response()->json([
            'status' => true,
            'data' => $hitchhikers,
        ]);
    }

    public function storeHitchhiker(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'gender' => 'required|max:50',
            'address' => 'required|max:224',
            'profile_image_url' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $profileImageUrl = null;
        if ($request->profile_image_url) {
            $imageData = base64_decode($request->profile_image_url);
            $imageName = uniqid() . '.png';
            $imagePath = public_path('storage/uploads/' . $imageName);
            file_put_contents($imagePath, $imageData);
            $profileImageUrl = asset('storage/uploads/' . $imageName);
        }

        $hitchhiker_info = new Hitchhiker();
        $hitchhiker_info->user_id = $request->user()->id;
        $hitchhiker_info->first_name = $request["first_name"];
        $hitchhiker_info->last_name = $request["last_name"];
        $hitchhiker_info->gender = $request["gender"];
        $hitchhiker_info->address = $request["address"];
        $hitchhiker_info->profile_image_url = $profileImageUrl;
        $hitchhiker_info->save();

        return response()->json([
            'status' => true,
            'message' => 'Hitchhiker added successfully',
            'data' => $hitchhiker_info,
        ], 201);
    }

    public function addRating(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role_id === 1) { //driver
            $driver = $user->driver;
            $ride = $driver->confirmedRides()->where('hitchhiker_id', $id)->latest()->first();

            if ($ride) {
                $ride->ride_status = "1";
                $ride->save();
            }

            $rating = new Rating();
            $rating->rating = $request["rating"];
            $rating->hitchhiker_id = $id;
            $rating->save();
        } elseif ($user->role_id === 0) { //hitchhiker

            $hitchhiker = $user->hitchhiker;
            $ride = $hitchhiker->confirmedRides()->where('driver_id', $id)->latest()->first();

            if ($ride) {
                $ride->ride_status = "1";
                $ride->save();
            }

            $rating = new Rating();
            $rating->rating = $request["rating"];
            $rating->driver_id = $id;
            $rating->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'rating added sucessfully',
        ], 200);
    }
}
