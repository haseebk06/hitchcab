<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Hitchhiker;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Ride;

class RideController extends Controller
{
    public function storeRides(Request $request)
    {
        $user = $request->user();

        if ($user->role_id === 1) {

            $validator = Validator::make($request->all(), [
                'origin_address' => 'required|max:224',
                'destination_address' => 'required|max:224',
                'origin_latitude' => 'required|max:224',
                'origin_longitude' => 'required|max:224',
                'destination_latitude' => 'required|max:224',
                'destination_longitude' => 'required|max:224',
                'ride_time' => 'required|max:224',
                'fare_price' => 'required|max:224',
            ]);
        } elseif ($user->role_id === 0) {

            $validator = Validator::make($request->all(), [
                'origin_address' => 'required|max:224',
                'destination_address' => 'required|max:224',
                'origin_latitude' => 'required|max:224',
                'origin_longitude' => 'required|max:224',
                'destination_latitude' => 'required|max:224',
                'destination_longitude' => 'required|max:224',
                'ride_time' => 'required|max:224',
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($user->role_id === 1) {
            $rides = $request->user()->driver->rides()->create($request->all());
        } elseif ($user->role_id === 0) {
            $rides = $request->user()->hitchhiker->rides()->create($request->all());
        }

        return response()->json([
            'status' => true,
            'message' => 'Driver added successfully',
            'data' => $rides,
        ], 201);
    }

    public function rides()
    {
        $rides = Ride::all();

        return response()->json([
            'status' => true,
            'message' => 'Rides fetched successfully',
            'data' => $rides,
        ], 201);
    }

    public function updateRide(Request $request)
    {
        $user = $request->user();

        if ($user->role_id === 1) {
            $driver = $user->driver;

            $ride = $driver->rides()->latest()->first();
            $ride->hitchhiker_id = $request['hitchhiker_id'];
            $ride->save();
        } elseif ($user->role_id === 0) {
            $hitchhiker = $user->hitchhiker;

            $ride = $hitchhiker->rides()->latest()->first();
            $ride->driver_id = $request['driver_id'];
            $ride->save();
        }

        return response()->json([
            'status' => true,
            'data' => $ride,
        ]);
    }

    public function completedRides(Request $request)
    {
        $user = $request->user();

        if ($user->role_id === 1) {  //driver
            $hitchhikers = Hitchhiker::with(['rides' => function ($query) use ($user) {
                $query->where('rides.driver_id', $user->driver->id);
            }])->whereHas('rides', function ($query) use ($user) {
                $query->where('rides.driver_id', $user->driver->id);
            })->get();            

            $ridesArray = [];

            foreach ($hitchhikers as $hitchhiker) {
                foreach ($hitchhiker->rides as $ride) {
                    $ridesArray[] = [
                        'ride_id' => $ride->id,
                        'context_id' => $ride->driver_id,
                        'updated_at' => $ride->updated_at,
                        'context_id' => $hitchhiker->id,
                        'ride_status' => $ride->ride_status,
                        'origin_address' => $ride->origin_address,
                        'hitchhiker_address' => $hitchhiker->address,
                        'destination_address' => $ride->destination_address,
                        'profile_image_url' => $hitchhiker->profile_image_url,
                        'full_name' => $hitchhiker->first_name . ' ' . $hitchhiker->last_name,
                    ];
                }
            }
        } elseif ($user->role_id === 0) {  //hitchhiker
            $drivers = Driver::with(['rides' => function ($query) use ($user) {
                $query->where('rides.hitchhiker_id', $user->hitchhiker->id);
            }])->whereHas('rides', function ($query) use ($user) {
                $query->where('rides.hitchhiker_id', $user->hitchhiker->id);
            })->get();

            $ridesArray = [];

            foreach ($drivers as $driver) {
                foreach ($driver->rides as $ride) {
                    $ridesArray[] = [
                        'ride_id' => $ride->id,
                        'context_id' => $driver->id,
                        'full_name' => $driver->first_name . ' ' . $driver->last_name,
                        'updated_at' => $ride->updated_at,
                        'fare_price' => $ride->fare_price,
                        'ride_status' => $ride->ride_status,
                        'hitchhiker_id' => $ride->hitchhiker_id,
                        'origin_address' => $ride->origin_address,
                        'destination_address' => $ride->destination_address,
                    ];
                }
            }
        }

        return response()->json([
            'status' => true,
            'data' => $ridesArray,
        ]);
    }
}
