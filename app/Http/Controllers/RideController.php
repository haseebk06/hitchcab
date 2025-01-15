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
