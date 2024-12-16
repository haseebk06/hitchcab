<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Driver;

class DriverController extends Controller
{
    public function getDriver()
    {
        $drivers = Driver::all();
        return response()->json([
            'status' => true,
            'data' => $drivers,
        ]);
    }
    public function storeDriver(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:224',
            'last_name' => 'required|max:224',
            'car_seats' => 'required|max:224',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $driver_info = $request->user()->driver()->create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Driver added successfully',
            'data' => $driver_info,
        ], 201);
    }
}
