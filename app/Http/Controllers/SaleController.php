<?php

namespace App\Http\Controllers;

use App\Models\HoldCart;
use App\Models\Sale;
use App\Models\Shift;
use App\Models\SoldItems;
use App\Models\Retrun;
use App\Models\RetrunItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    public function getSales()
    {
        $sales = Sale::with(['soldItems' => function ($query) {
            $query->where('is_return', false);
        }])->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Sales fetched successfully',
            'data' => $sales,
        ], 200);
    }
    
    public function getCurrentShiftSales($id, $userId)
    {
        $sales = Sale::where('user_id', $userId)
            ->where('shift_id', $id)
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();
    
        $totalSales = Sale::where('user_id', $userId)
            ->where('shift_id', $id)
            ->whereDate('created_at', today())
            ->sum('finalTotal');
    
        $totalGrossSales = Sale::where('user_id', $userId)
            ->where('shift_id', $id)
            ->whereDate('created_at', today())
            ->sum('total');
    
        $totalGst = Sale::where('user_id', $userId)
            ->where('shift_id', $id)
            ->whereDate('created_at', today())
            ->sum('gst');
    
        $totalServiceCharges = Sale::where('user_id', $userId)
            ->where('shift_id', $id)
            ->whereDate('created_at', today())
            ->sum('service_charges');
    
        return response()->json([
            'status' => true,
            'message' => 'Sales fetched successfully',
            'data' => $sales,
            'total_sales' => $totalSales,
            'gross_sales' => $totalGrossSales,
            'total_gst' => $totalGst,
            'total_service_charges' => $totalServiceCharges,
        ], 200);
    }
        
    public function getCurrentShiftRetruns($id, $userId)
    {
        $returns = Retrun::where('user_id', $userId)
            ->where('shift_id', $id)
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRetruns = Retrun::where('user_id', $userId)
            ->where('shift_id', $id)
            ->whereDate('created_at', today())
            ->sum('finalTotal');

        $totalGrossRetruns = Retrun::where('user_id', $userId)
            ->where('shift_id', $id)
            ->whereDate('created_at', today())
            ->sum('total');
    
        $totalGst = Retrun::where('user_id', $userId)
            ->where('shift_id', $id)
            ->whereDate('created_at', today())
            ->sum('gst');
    
        $totalServiceCharges = Retrun::where('user_id', $userId)
            ->where('shift_id', $id)
            ->whereDate('created_at', today())
            ->sum('service_charges');

        return response()->json([
            'status' => true,
            'message' => 'retruns fetched successfully',
            'data' => $returns,
            'total_retruns' => $totalRetruns,
            'gross_retruns' => $totalGrossRetruns,
            'total_gst' => $totalGst,
            'total_service_charges' => $totalServiceCharges,
        ], 200);
    }

    public function getPreviousShiftSales($counterId, $userId)
    {
        $previousShift = Shift::where('status', 'closed')
            ->where('counter_id', $counterId)
            ->whereDate('start_time', today())
            ->orderBy('end_time', 'desc')
            ->first();
    
        if (!$previousShift) {
            return response()->json([
                'status' => false,
                'message' => 'No previous shift found for this counter today',
                'shift_id' => null,
                'data' => [],
                'total_sales' => 0,
                'gross_sales' => 0,
                'total_gst' => 0,
                'total_service_charges' => 0,
            ], 200);
        }
    
        $sales = Sale::where('shift_id', $previousShift->id)
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();
    
        $totalSales = Sale::where('shift_id', $previousShift->id)
            ->whereDate('created_at', today())
            ->sum('finalTotal');
    
        $totalGrossSales = Sale::where('shift_id', $previousShift->id)
            ->whereDate('created_at', today())
            ->sum('total');
            
        $totalGst = Sale::where('shift_id', $previousShift->id)
            ->whereDate('created_at', today())
            ->sum('gst');
    
        $totalServiceCharges = Sale::where('shift_id', $previousShift->id)
            ->whereDate('created_at', today())
            ->sum('service_charges');
    
        return response()->json([
            'status' => true,
            'message' => 'Previous shift sales fetched successfully',
            'shift_id' => $previousShift->id,
            'data' => $sales,
            'total_sales' => $totalSales,
            'gross_sales' => $totalGrossSales,
            'total_gst' => $totalGst,
            'total_service_charges' => $totalServiceCharges,
        ], 200);
    }
    
    public function getPreviousShiftRetruns($counterId, $userId)
    {
        $previousShift = Shift::where('status', 'closed')
            ->where('counter_id', $counterId)
            ->whereDate('start_time', today())
            ->orderBy('end_time', 'desc')
            ->first();

        if (!$previousShift) {
            return response()->json([
                'status' => false,
                'message' => 'No previous shift found for this counter today',
                'shift_id' => null,
                'data' => [],
                'total_sales' => 0,
            ], 200);
        }

        $returns = Retrun::where('shift_id', $previousShift->id)
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRetruns = Retrun::where('shift_id', $previousShift->id)
            ->whereDate('created_at', today())
            ->sum('finalTotal');

        $totalGrossRetruns = Retrun::where('shift_id', $previousShift->id)
            ->whereDate('created_at', today())
            ->sum('total');
    
        $totalGst = Retrun::where('shift_id', $previousShift->id)
            ->whereDate('created_at', today())
            ->sum('gst');
    
        $totalServiceCharges = Retrun::where('shift_id', $previousShift->id)
            ->whereDate('created_at', today())
            ->sum('service_charges');
    
        return response()->json([
            'status' => true,
            'message' => 'Previous shift retrun fetched successfully',
            'shift_id' => $previousShift->id,
            'data' => $returns,
            'total_retruns' => $totalRetruns,
            'gross_retruns' => $totalGrossRetruns,
            'total_gst' => $totalGst,
            'total_service_charges' => $totalServiceCharges,
        ], 200);
    }
    
    public function getCurrentShiftItemsSold($shiftId, $userId)
    {
        $sales = Sale::where('user_id', $userId)
            ->where('shift_id', $shiftId)
            ->whereDate('created_at', today())
            ->with(['soldItems' => function($query) {
                $query->where('is_return', false);
            }])
            ->get();
    
        $itemsSold = [];
        
        foreach ($sales as $sale) {
            foreach ($sale->soldItems as $item) {
                $itemName = $item->name;
                
                if (!isset($itemsSold[$itemName])) {
                    $itemsSold[$itemName] = [
                        'name' => $itemName,
                        'quantity' => 0,
                        'total_amount' => 0,
                        'category' => $item->category,
                        'unit' => $item->unit
                    ];
                }
                
                $itemsSold[$itemName]['quantity'] += $item->quantity;
                $itemsSold[$itemName]['total_amount'] += $item->subtotal;
            }
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Current shift items sold fetched successfully',
            'data' => array_values($itemsSold),
        ], 200);
    }

    public function getPreviousShiftItemsSold($counterId, $userId)
    {
        $previousShift = Shift::where('status', 'closed')
            ->where('counter_id', $counterId)
            ->whereDate('start_time', today())
            ->orderBy('end_time', 'desc')
            ->first();
    
        if (!$previousShift) {
            return response()->json([
                'status' => false,
                'message' => 'No previous shift found for this counter today',
                'data' => [],
            ], 200);
        }
    
        $sales = Sale::where('shift_id', $previousShift->id)
            ->whereDate('created_at', today())
            ->with(['soldItems' => function($query) {
                $query->where('is_return', false);
            }])
            ->get();
    
        $itemsSold = [];
        
        foreach ($sales as $sale) {
            foreach ($sale->soldItems as $item) {
                $itemName = $item->name;
                
                if (!isset($itemsSold[$itemName])) {
                    $itemsSold[$itemName] = [
                        'name' => $itemName,
                        'quantity' => 0,
                        'total_amount' => 0,
                        'category' => $item->category,
                        'unit' => $item->unit
                    ];
                }
                
                $itemsSold[$itemName]['quantity'] += $item->quantity;
                $itemsSold[$itemName]['total_amount'] += $item->subtotal;
            }
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Previous shift items sold fetched successfully',
            'data' => array_values($itemsSold),
        ], 200);
    }

    public function getAllTransactions()
    {
        $sales = Sale::with(['soldItems' => function ($query) {
            $query->where('is_return', false);
        }])
            ->where('is_return', false)
            ->whereNull('return_reason')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Sales fetched successfully',
            'data' => $sales,
        ], 200);
    }

    public function getReturns()
    {
        $returns = Retrun::with('retrunItems')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Returns fetched successfully',
            'data' => $returns,
        ], 200);
    }
    
    public function addReturns(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sale_id' => 'required|exists:sales,id',
            'shift_id' => 'required|exists:shifts,id',
            'reason' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $originalSale = Sale::with(['soldItems' => function ($query) {
                $query->where('is_return', false);
            }])->findOrFail($request->sale_id);

            if ($originalSale->is_return || $originalSale->status === 'returned') {
                throw new \Exception('This sale has already been fully returned');
            }

            $returnLines = [];
            $returnTotal = 0;

            foreach ($request->items as $item) {
                $soldItem = null;

                if (!empty($item['sold_item_id'])) {
                    $soldItem = $originalSale->soldItems->firstWhere('id', (int) $item['sold_item_id']);
                }

                if (!$soldItem) {
                    $soldItem = $originalSale->soldItems
                        ->where('name', $item['name'] ?? null)
                        ->when(!empty($item['barcode']), function ($items) use ($item) {
                            return $items->where('barcode', $item['barcode']);
                        })
                        ->first();
                }

                if (!$soldItem) {
                    throw new \Exception('Return item was not found in the original sale');
                }

                $returnQuantity = (int) $item['quantity'];

                if ($returnQuantity > (int) $soldItem->quantity) {
                    throw new \Exception("Return quantity cannot exceed sold quantity for {$soldItem->name}");
                }

                $subtotal = round((float) $soldItem->sellingPrice * $returnQuantity, 2);
                $returnTotal += $subtotal;

                $returnLines[] = [
                    'sold_item' => $soldItem,
                    'quantity' => $returnQuantity,
                    'subtotal' => $subtotal,
                ];
            }

            $originalTotal = max((float) $originalSale->total, 0);
            $returnRatio = $originalTotal > 0 ? min($returnTotal / $originalTotal, 1) : 0;
            $returnTax = round((float) $originalSale->tax * $returnRatio, 2);
            $returnGst = round((float) $originalSale->gst * $returnRatio, 2);
            $returnServiceCharges = round((float) $originalSale->service_charges * $returnRatio, 2);
            $returnDiscount = round(min((float) $originalSale->discount * $returnRatio, $returnTotal), 2);
            $calculatedFinalTotal = $returnTotal + $returnTax + $returnGst + $returnServiceCharges - $returnDiscount;
            $returnFinalTotal = round(max(0, (float) $originalSale->finalTotal > 0
                ? (float) $originalSale->finalTotal * $returnRatio
                : $calculatedFinalTotal
            ), 2);

            // Create the return record
            $return = new Retrun();
            $return->user_id = $request->user()->id;
            $return->sale_id = $originalSale->id;
            $return->total = $returnTotal;
            $return->tax = $returnTax;
            $return->gst = $returnGst;
            $return->service_charges = $returnServiceCharges;
            $return->shift_id = $request->shift_id;
            $return->discount = $returnDiscount;
            $return->finalTotal = $returnFinalTotal;
            $return->paymentMethod = $originalSale->paymentMethod;
            $return->amountReceived = $returnFinalTotal;
            $return->changeAmount = 0;
            $return->reason = $request->reason;
            $return->save();
    
            // Save return items and update original sold items
            $savedItems = [];
            foreach ($returnLines as $line) {
                $soldItem = $line['sold_item'];

                // Create return item record
                $returnItem = new RetrunItem();
                $returnItem->return_id = $return->id;
                $returnItem->name = $soldItem->name;
                $returnItem->quantity = $line['quantity'];
                $returnItem->barcode = $soldItem->barcode;
                $returnItem->category = $soldItem->category;
                $returnItem->costPrice = $soldItem->costPrice;
                $returnItem->sellingPrice = $soldItem->sellingPrice;
                $returnItem->stock = $soldItem->stock;
                $returnItem->subtotal = $line['subtotal'];
                $returnItem->unit = $soldItem->unit;
                $returnItem->save();
    
                $savedItems[] = $returnItem;
    
                $this->updateSoldItemReturnStatus($soldItem, $line['quantity'], $request->reason);
                $this->restoreStock($soldItem->barcode, $line['quantity']);
            }
    
            // Update the original sale status based on return type
            $originalSale->refresh();
            $isFullReturn = $this->isFullReturn($originalSale->id);

            if ($isFullReturn) {
                $originalSale->status = 'returned';
                $originalSale->return_reason = $request->reason;
                $originalSale->save();
            }
    
            DB::commit();
    
            return response()->json([
                'status' => true,
                'message' => 'Return processed successfully',
                'data' => [
                    'return' => $return,
                    'items' => $savedItems,
                    'updated_sale' => $originalSale
                ],
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to process return: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Helper method to update sold item's return status
    private function updateSoldItemReturnStatus(SoldItems $soldItem, int $returnedQuantity, string $reason)
    {
        if ($returnedQuantity >= (int) $soldItem->quantity) {
            $soldItem->is_return = 1;
            $soldItem->return_reason = $reason;
        } else {
            $soldItem->quantity -= $returnedQuantity;
            $soldItem->subtotal = round((float) $soldItem->sellingPrice * (int) $soldItem->quantity, 2);
            $soldItem->is_return = 0;
            $soldItem->return_reason = 'Partially returned: ' . $returnedQuantity . ' item(s). ' . $reason;
        }

        $soldItem->save();
    }

    private function restoreStock(?string $barcode, int $quantity)
    {
        if (!$barcode) {
            return;
        }

        $stock = Stock::where('barcode', $barcode)->first();

        if ($stock) {
            $stock->stock = (int) $stock->stock + $quantity;
            $stock->save();
        }
    }

    private function isFullReturn($saleId)
    {
        // Get all non-returned items from the original sale
        $originalItems = SoldItems::where('sale_id', $saleId)
                                 ->where('is_return', 0)
                                 ->get();
        
        // If there are no non-returned items left, it's a full return
        return $originalItems->isEmpty();
    }

    public function getHoldItems(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'holdId' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $holdId = $request->input('holdId');
        $holdItems = HoldCart::where('holdId', $holdId)->get();

        if ($holdItems->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No items found for this Hold ID'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Hold items loaded',
            'data' => $holdItems,
        ], 200);
    }

    public function addSales(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total' => 'required|max:255',
            'tax' => 'required|max:255',
            'discount' => 'required|max:255',
            'finalTotal' => 'required|max:255',
            'paymentMethod' => 'required|max:255',
            'amountReceived' => 'required|max:255',
            'changeAmount' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $sale = new Sale();
        $sale->user_id = $request->user()->id;
        $sale->total = $request["total"];
        $sale->tax = $request["tax"];
        $sale->gst = $request["gst"];
        $sale->service_charges = $request["service_charges"];
        $sale->shift_id = $request["shift_id"];
        $sale->discount = $request["discount"];
        $sale->finalTotal = $request["finalTotal"];
        $sale->paymentMethod = $request["paymentMethod"];
        $sale->amountReceived = $request["amountReceived"];
        $sale->changeAmount = $request["changeAmount"];
        $sale->mode = $request["mode"];
        $sale->save();

        return response()->json([
            'status' => true,
            'message' => 'Stock added successfully',
            'data' => $sale,
        ], 200);
    }

    public function addSoldItems(Request $request)
    {
        $user = $request->user();
        $sale = $user->Sales()->latest()->first();

        if (!$sale) {
            return response()->json([
                'status' => false,
                'message' => 'No sale record found for the user.'
            ], 404);
        }

        $saleId = $sale->id;

        $savedItems = [];

        foreach ($request->items as $item) {
            $soldItem = new SoldItems();
            $soldItem->name = $item['name'];
            $soldItem->quantity = $item['quantity'];
            $soldItem->barcode = $item['barcode'] ?? null;
            $soldItem->category = $item['category'] ?? null;
            $soldItem->costPrice = $item['costPrice'];
            $soldItem->sellingPrice = $item['sellingPrice'];
            $soldItem->stock = $item['stock'];
            $soldItem->subtotal = $item['subtotal'];
            $soldItem->unit = $item['unit'] ?? null;
            $soldItem->sale_id = $saleId;
            $soldItem->save();

            $savedItems[] = $soldItem;
        }


        return response()->json([
            'status' => true,
            'message' => 'SoldItems added successfully',
            'data' => $savedItems,
        ], 200);
    }

    public function addHoldItems(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'holdId' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $holdId = $request->holdId;

        $user = $request->user();

        $holdItems = [];

        foreach ($request->items as $item) {
            $holdItem = new HoldCart();
            $holdItem->holdId = $holdId;
            $holdItem->name = $item['name'];
            $holdItem->quantity = $item['quantity'];
            $holdItem->barcode = $item['barcode'] ?? null;
            $holdItem->category = $item['category'] ?? null;
            $holdItem->costPrice = $item['costPrice'];
            $holdItem->sellingPrice = $item['sellingPrice'];
            $holdItem->stock = $item['stock'];
            $holdItem->subtotal = $item['subtotal'];
            $holdItem->unit = $item['unit'] ?? null;
            $holdItem->user_id = $user->id;
            $holdItem->save();

            $holdItems[] = $holdItem;
        }


        return response()->json([
            'status' => true,
            'message' => 'Hold added successfully',
            'data' => $holdItems,
        ], 200);
    }

    public function destroy($id)
    {
        $holdCart = HoldCart::where('holdId', $id);

        if (!$holdCart) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $holdCart->delete();

        return response()->json([
            'status' => true,
            'message' => 'Hold Item deleted successfully'
        ], 200);
    }

    // Return
    public function returnItem(Request $request)
    {
        $validated = $request->validate([
            'saleId' => 'required|exists:sales,id',
            'itemId' => 'required|exists:sold_items,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            $originalSale = Sale::with('soldItems')->findOrFail($validated['saleId']);
            $item = SoldItems::findOrFail($validated['itemId']);

            // Validate quantity
            if ($validated['quantity'] > $item->quantity) {
                throw new \Exception('Return quantity cannot exceed original sale quantity');
            }

            // Create a return record for the item
            $returnItem = new SoldItems();
            $returnItem->sale_id = $originalSale->id;
            $returnItem->name = $item->name;
            $returnItem->quantity = -$validated['quantity'];
            $returnItem->barcode = $item->barcode;
            $returnItem->category = $item->category;
            $returnItem->costPrice = $item->costPrice;
            $returnItem->sellingPrice = $item->sellingPrice;
            $returnItem->stock = 999;
            $returnItem->subtotal = - ($item->sellingPrice * $validated['quantity']);
            $returnItem->unit = $item->unit;
            $returnItem->is_return = true;
            $returnItem->return_reason = $validated['reason'];
            $returnItem->save();

            // Update original item quantity if partial return
            if ($validated['quantity'] < $item->quantity) {
                $item->quantity -= $validated['quantity'];
                $item->subtotal = $item->sellingPrice * $item->quantity;
                $item->save();
            } else {
                $item->delete(); // Full return - remove original item
            }

            // Recalculate sale totals
            $this->recalculateSaleTotals($originalSale);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Item returned successfully',
                'data' => $originalSale->fresh(['soldItems'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    protected function recalculateSaleTotals(Sale $sale)
    {
        $sale->load('soldItems');

        // Calculate new totals from non-returned items only
        $sale->total = $sale->soldItems->where('is_return', false)->sum('subtotal');
        $sale->finalTotal = $sale->total + $sale->tax - $sale->discount;
        $sale->save();
    }

       public function returnEntireSale(Request $request, $id)
    {
        $validated = $request->validate([
            'saleId' => 'required|exists:sales,id',
            'reason' => 'required|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            $originalSale = Sale::with('soldItems')->findOrFail($validated['saleId']);

            // Validate sale can be returned
            if ($originalSale->is_return) {
                throw new \Exception('Cannot return an already returned sale');
            }

            if ($originalSale->status === 'refunded') {
                throw new \Exception('This sale has already been fully refunded');
            }

            // 1. Update original sale status
            $originalSale->update([
                'status' => 'returned',
                'return_reason' => $validated['reason'],
            ]);

            // 2. Create the return sale (with negative amounts)
            $returnSale = Sale::create([
                'total' => -$originalSale->total,
                'tax' => -$originalSale->tax,
                'discount' => -$originalSale->discount,
                'finalTotal' => -$originalSale->finalTotal,
                'paymentMethod' => 'return',
                'amountReceived' => 0,
                'changeAmount' => 0,
                'original_sale_id' => $originalSale->id,
                'return_reason' => $validated['reason'],
                'user_id' => $request->user()->id,
                'shift_id' => $id,
                'is_return' => true,
                'status' => 'refunded'
            ]);

            // 3. Process each item
            foreach ($originalSale->soldItems as $item) {
                
                // Create return item record
                SoldItems::create([
                    'name' => $item->name,
                    'quantity' => -$item->quantity,
                    'original_quantity' => $item->quantity,
                    'barcode' => $item->barcode,
                    'category' => $item->category,
                    'costPrice' => $item->costPrice,
                    'sellingPrice' => $item->sellingPrice,
                    'stock' => $product ? $product->stock : 0,
                    'subtotal' => -$item->subtotal,
                    'unit' => $item->unit,
                    'return_reason' => $validated['reason'],
                    'sale_id' => $returnSale->id
                ]);

                // Mark original item as returned
                $item->update(['is_return' => true]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Entire sale returned successfully',
                'data' => [
                    'original_sale' => $originalSale,
                    'return_sale' => $returnSale
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
