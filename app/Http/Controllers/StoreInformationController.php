<?php

namespace App\Http\Controllers;

use App\Models\StoreInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreInformationController extends Controller
{
    public function getStoreInfo()
    {
        $storeInformation = StoreInformation::all();

        return response()->json([
            'status' => true,
            'message' => 'Store Information fetched successfully',
            'data' => $storeInformation,
        ], 201);
    }

    public function addStoreInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'storeName' => 'required|unique:store_information,storeName|max:255',
            'address' => 'required|max:255',
            'phone' => 'required|max:255',
            'email' => 'unique:store_information,email|max:255',
            'currency' => 'required|max:255',
            'sst' => 'nullable|max:255',
            'wh_tax_percentage' => 'nullable|numeric|min:0',
            'sst_withholding_tax_percentage' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $storeInfo = StoreInformation::create($request->all());
        $storeInfo->save();

        return response()->json([
            'status' => true,
            'message' => 'Store Information added successfully',
            'data' => $storeInfo,
        ], 200);
    }

    public function updateStoreInfo(Request $request, $id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'storeName' => 'sometimes|required|max:255|unique:store_information,storeName,' . $id,
            'address' => 'sometimes|required|max:255',
            'phone' => 'sometimes|required|max:255',
            'email' => 'sometimes|nullable|max:255|unique:store_information,email,' . $id,
            'taxId' => 'sometimes|nullable|max:255',
            'logo' => 'sometimes|nullable|max:255',
            'currency' => 'sometimes|required|max:255',
            'sst' => 'sometimes|max:255',
            'wh_tax_percentage' => 'sometimes|nullable|numeric|min:0',
            'sst_withholding_tax_percentage' => 'sometimes|nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $storeInfo = StoreInformation::find($id);

        if (!$storeInfo) {
            return response()->json([
                'status' => false,
                'message' => 'Store Information not found',
            ], 404);
        }

        // Update only the fields that were provided in the request
        $storeInfo->fill($request->only([
            'storeName',
            'address',
            'phone',
            'email',
            'taxId',
            'logo',
            'currency',
            'sst',
            'wh_tax_percentage',
            'sst_withholding_tax_percentage'
        ]));

        $storeInfo->save();

        return response()->json([
            'status' => true,
            'message' => 'Store Information updated successfully',
            'data' => $storeInfo,
        ], 200);
    }
}
