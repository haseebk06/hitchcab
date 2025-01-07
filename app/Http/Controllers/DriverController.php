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
            'profile_image_url' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'car_image_url' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('profile_image_url')) {
            $imageProfile = $request->file('profile_image_url');
            $imagePathProfile = $imageProfile->store('uploads', 'public');
            $profileImageUrl = asset('storage/' . $imagePathProfile);
        }

        if ($request->hasFile('car_image_url')) {
            $imageCar = $request->file('car_image_url');
            $imagePathCar = $imageCar->store('uploads', 'public');
            $carImageUrl = asset('storage/' . $imagePathCar);
        }

        $driver_info = new Driver();
        $driver_info->user_id = $request->user()->id;
        $driver_info->first_name = $request["first_name"];
        $driver_info->last_name = $request["last_name"];
        $driver_info->gender = $request["gender"];
        $driver_info->address = $request["address"];
        $driver_info->profile_image_url = $profileImageUrl;
        $driver_info->car_image_url = $carImageUrl;
        $driver_info->car_seats = $request["car_seats"];
        $driver_info->save();

        return response()->json([
            'status' => true,
            'message' => 'Driver added successfully',
            'data' => $driver_info,
        ], 201);
    }
}
