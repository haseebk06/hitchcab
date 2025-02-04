<?php

namespace App\Http\Controllers;

use App\Models\ConfirmedRide;
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

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $vehicleInfo = $user->vehicleInformations()->select('vehicle_average')->first();

        if (!$vehicleInfo) {
            return response()->json(['status' => false, 'message' => 'Vehicle information not found'], 404);
        }

        $vehicleAverage = floatval($vehicleInfo->vehicle_average);

        $fuelPricePerLiter = 256.13;

        if ($user->role_id === 1) { //driver

            $validator = Validator::make($request->all(), [
                'origin_address' => 'required|max:224',
                'destination_address' => 'required|max:224',
                'origin_latitude' => 'required|max:224',
                'origin_longitude' => 'required|max:224',
                'destination_latitude' => 'required|max:224',
                'destination_longitude' => 'required|max:224',
                'ride_time' => 'required|numeric|max:224',
                'ride_distance' => 'required|numeric|max:224',
                'fare_price' => 'required|max:224',
            ]);
        } elseif ($user->role_id === 0) { //hitchhiker

            $validator = Validator::make($request->all(), [
                'origin_address' => 'required|max:224',
                'destination_address' => 'required|max:224',
                'origin_latitude' => 'required|max:224',
                'origin_longitude' => 'required|max:224',
                'destination_latitude' => 'required|max:224',
                'destination_longitude' => 'required|max:224',
                'ride_time' => 'required|numeric|max:224',
                'ride_distance' => 'required|numeric|max:224',
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $rideDistance = floatval($request->ride_distance);
        $fuelRequired = $rideDistance / $vehicleAverage;
        $fuelCost = round($fuelRequired * $fuelPricePerLiter);
        
        // Add 20% driver bonus to the fuel cost
        $adjustedFuelCost = round($fuelCost * 1.20); 

        if ($request->fare_price > $adjustedFuelCost) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => [
                    'fare_price' => [
                        "Please use average fare: (Rs $adjustedFuelCost)."
                    ]
                ]
            ], 422);
        }

        if ($user->role_id === 1) {
            $rides = $user->driver->rides()->create($request->all());
        } elseif ($user->role_id === 0) {
            $rides = $user->hitchhiker->rides()->create($request->all());
        }

        return response()->json([
            'status' => true,
            'message' => 'Ride added successfully',
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
        $ride = new ConfirmedRide();

        if ($user->role_id === 1) { //driver

            $ride->origin_address = $request['origin_address'];
            $ride->destination_address = $request['destination_address'];
            $ride->driver_id = $user->driver->id;
            $ride->hitchhiker_id = $request['hitchhiker_id'];
            $ride->save();
        } elseif ($user->role_id === 0) { //hitchhiker

            // dd($request->all());
            $ride->origin_address = $request['origin_address'];
            $ride->destination_address = $request['destination_address'];
            $ride->driver_id = $request['driver_id'];
            $ride->hitchhiker_id = $user->hitchhiker->id;
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
            $confirmedRides = ConfirmedRide::with('hitchhiker')->where('driver_id', $user->driver->id)->get();

            $ridesArray = [];

            foreach ($confirmedRides as $ride) {
                $ridesArray[] = [
                    'ride_id' => $ride->id,
                    'context_id' => $ride->hitchhiker->id,
                    'full_name' => $ride->hitchhiker->first_name . ' ' . $ride->hitchhiker->last_name,
                    'updated_at' => $ride->updated_at,
                    'fare_price' => $ride->fare_price,
                    'ride_status' => $ride->ride_status,
                    'driver_id' => $ride->driver_id,
                    'origin_address' => $ride->origin_address,
                    'destination_address' => $ride->destination_address,
                ];
            }
        } elseif ($user->role_id === 0) {  //hitchhiker
            $confirmedRides = ConfirmedRide::with('driver')->where('hitchhiker_id', $user->hitchhiker->id)->get();

            $ridesArray = [];

            foreach ($confirmedRides as $ride) {
                $ridesArray[] = [
                    'ride_id' => $ride->id,
                    'context_id' => $ride->driver->id,
                    'full_name' => $ride->driver->first_name . ' ' . $ride->driver->last_name,
                    'updated_at' => $ride->updated_at,
                    'fare_price' => $ride->fare_price,
                    'ride_status' => $ride->ride_status,
                    'hitchhiker_id' => $ride->hitchhiker_id,
                    'origin_address' => $ride->origin_address,
                    'destination_address' => $ride->destination_address,
                ];
            }
        }

        return response()->json([
            'status' => true,
            'data' => $ridesArray,
        ]);
    }
}
