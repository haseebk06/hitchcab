<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Driver;

class DriverController extends Controller
{
    public function getDriver()
    {
        $drivers = Driver::whereHas('rides', function ($query) {
            $query->where('fare_price', '!=', null);
            // ->where('created_at', '>=', now()->subMinutes(10)); 
        })
            ->with([
                'rides' => function ($query) {
                    $query->where('fare_price', '!=', null)
                        // ->where('created_at', '>=', now()->subMinutes(10))  
                        ->latest()
                        ->limit(1);
                },
                'user' => function ($query) { // Assuming 'user' is the relationship name in the Driver model
                    $query->select('id', 'phone_number'); // Fetch only necessary fields
                }
            ])
            ->get();

        return response()->json([
            'status' => true,
            'data' => $drivers,
        ]);
    }

    public function storeDriver(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'gender' => 'required|max:50',
            'address' => 'required|max:224',
            'car_seats' => 'required|max:224',
            'profile_image_url' => 'required|string', // Validate base64 string
            'car_image_url' => 'required|string',    // Validate base64 string
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
            // Decode the base64 profile image
            $imageData = base64_decode($request->profile_image_url);
            $imageName = uniqid() . '_profile.png';
            $imagePath = public_path('storage/uploads/' . $imageName);
            file_put_contents($imagePath, $imageData);
            $profileImageUrl = asset('storage/uploads/' . $imageName);
        }

        $carImageUrl = null;
        if ($request->car_image_url) {
            // Decode the base64 car image
            $imageData = base64_decode($request->car_image_url);
            $imageName = uniqid() . '_car.png';
            $imagePath = public_path('storage/uploads/' . $imageName);
            file_put_contents($imagePath, $imageData);
            $carImageUrl = asset('storage/uploads/' . $imageName);
        }

        $driver_info = new Driver();
        $driver_info->user_id = $request->user()->id;
        $driver_info->first_name = $request["first_name"];
        $driver_info->last_name = $request["last_name"];
        $driver_info->gender = $request["gender"];
        $driver_info->address = $request["address"];
        $driver_info->car_seats = $request["car_seats"];
        $driver_info->profile_image_url = $profileImageUrl;
        $driver_info->car_image_url = $carImageUrl;
        $driver_info->save();

        return response()->json([
            'status' => true,
            'message' => 'Driver added successfully',
            'data' => $driver_info,
        ], 201);
    }
}
