<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class VechicleController extends Controller
{

    public function addLicenseDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:224',
            'last_name' => 'required|max:224',
            'license_number' => 'required|unique:license_details|min:13|max:13',
            'issue_country' => 'required|max:224',
            'issue_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $licenseDetails = $request->user()->licenseDetails()->create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Details added successfully',
            'data' => $licenseDetails,
        ], 201);
    }

    public function vehicleInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'make' => 'required|max:224',
            'model' => 'required|max:224',
            'color' => 'required|max:224',
            'vehicle_average' => 'required|max:224',
            'license_plate_number' => 'required|unique:vehicle_information|string|regex:/^[A-Za-z]+-[0-9]+$/|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $vehicleInfo = $request->user()->vehicleInformations()->create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Info added successfully',
            'data' => $vehicleInfo,
        ], 201);
    }

    public function supportingDocs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_one' => 'required|min:13|max:224',
            'license_number' => 'required|max:224',
            'page_two' => 'required|max:224',
            'front' => 'required|max:224',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $supportingDocs = $request->user()->supportingDocuments()->create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Docs added successfully',
            'data' => $supportingDocs,
        ], 201);
    }
}
