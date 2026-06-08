<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Shift;
use App\Models\DailyReport;
use App\Models\Retrun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan;

class ShiftController extends Controller
{
    public function openShift(Request $request)
    {
        // 1. Check if user already has an open shift
        $existingShift = Shift::where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return response()->json(['message' => 'You already have an open shift.'], 400);
        }

        // 2. Check if this is the first shift of the day for this counter
        $today = now()->toDateString();
        $firstShiftToday = !Shift::where('counter_id', $request->counter_id)
            ->whereDate('start_time', $today)
            ->exists();

        // 3. Create shift
        $shift = Shift::create([
            'user_id'      => $request->user()->id,
            'counter_id'   => $request->counter_id,
            'name'         => $request->user()->name ?? 'Shift ' . now()->format('d-m-Y H:i'),
            'start_time'   => now(),
            'opening_cash' => $request->opening_cash ?? 0,
            'status'       => 'open',
        ]);

        // 4. Add flag in response
        return response()->json([
            'status' => true,
            'shift' => $shift,
            'is_first_shift' => $firstShiftToday,
        ], 201);
    }

    // Close a shift
    public function closeShift(Request $request, $id)
    {
        $shift = Shift::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->first();

        if (! $shift) {
            return response()->json(['message' => 'No open shift found.'], 404);
        }

        $shift->update([
            'end_time'       => now(),
            'closing_cash'   => $request->closing_cash ?? 0,
            'total_sales'    => $request->total_sales ?? 0,
            'total_expenses' => $request->total_expenses ?? 0,
            'status'         => 'closed',
        ]);

        return response()->json($shift);
    }

    public function currentShift($id)
    {
        $shift = Shift::where('user_id', $id)
            ->where('status', 'open')
            ->first();

        return response()->json($shift ?? ['message' => 'No open shift']);
    }
    
    public function prevShift($id)
    {
        $shift = Shift::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->where('status', 'closed')
            ->first();

        return response()->json($shift ?? ['message' => 'No closed shift']);
    }

    // List all shifts for user
    public function allShiftsById($id)
    {
        $shifts = Shift::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($shifts);
    }

    public function allShifts()
    {
        $today = today();

        // 1. Counter wise totals
        $totals = Shift::with('counter')
            ->whereDate('start_time', $today)
            ->selectRaw('counter_id, SUM(closing_cash) as total_closing_cash, SUM(total_sales) as total_sales')
            ->groupBy('counter_id')
            ->get();

        // 2. All shifts of today
        $shifts = Shift::with('counter')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'totals' => $totals,
            'shifts' => $shifts
        ]);
    }
    
    public function dailyReports()
    {
        $reports = DailyReport::with('counter')
            ->orderBy('report_date', 'desc')
            ->get();

        return response()->json($reports);
    }
    
    public function addCounter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'branch' => 'required|string|max:255',
            'system_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $counter = Counter::create([
            'name' => $request->name,
            'branch' => $request->branch,
            'status' => 'closed',
            'system_id' => $request->system_id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Counter created successfully.',
            'data' => $counter
        ], 201);
    }

    public function allCounters()
    {
        $counters = Counter::orderBy('created_at', 'desc')->get();

        return response()->json($counters);
    }
    
    public function openCounter(Request $request, $id)
    {

        $counter = Counter::where('id', $id)
            ->where('status', 'closed')
            ->first();

        if (! $counter) {
            return response()->json(['message' => 'No closed counter found.'], 404);
        }

        $counter->update([
            'start_time'      => now(),
            'opened_by'       => $request->user()->name,
            'closed_by'       => null,
            'status'          => 'open',
        ]);

        return response()->json($counter);
    }

    public function closeCounter(Request $request, $id)
    {
        $counter = Counter::where('id', $id)
            ->where('status', 'open')
            ->first();

        if (! $counter) {
            return response()->json(['message' => 'No open counter found.'], 404);
        }

        $counter->update([
            'end_time'       => now(),
            'closed_by'       => $request->user()->name,
            'status'         => 'closed',
        ]);

        return response()->json($counter);
    }

    public function todayCounterReport($id)
    {
        $counter = Counter::find($id);

        if (!$counter) {
            return response()->json(['message' => 'Counter not found.'], 404);
        }

        $shifts = Shift::with([
            'user',
            'sales.soldItems',
            'returns.retrunItems',
        ])
            ->where('counter_id', $counter->id)
            ->whereDate('start_time', today())
            ->orderBy('start_time')
            ->get();

        $reportShifts = $shifts->map(function ($shift, $index) {
            $itemRows = [];

            foreach ($shift->sales as $sale) {
                foreach ($sale->soldItems as $item) {
                    $key = $item->name;

                    if (!isset($itemRows[$key])) {
                        $itemRows[$key] = [
                            'name' => $item->name,
                            'quantity' => 0,
                            'amount' => 0,
                        ];
                    }

                    $itemRows[$key]['quantity'] += (int) $item->quantity;
                    $itemRows[$key]['amount'] += round((float) $item->subtotal);
                }
            }

            $returns = $shift->returns;

            return [
                'id' => $shift->id,
                'name' => 'Shift ' . ($index + 1),
                'cashier_name' => $shift->user->name ?? 'Unknown',
                'start_time' => $shift->start_time,
                'end_time' => $shift->end_time,
                'opening_cash' => round((float) $shift->opening_cash),
                'closing_cash' => round((float) $shift->closing_cash),
                'sales_total' => round((float) $shift->sales->sum('finalTotal')),
                'gross_total' => round((float) $shift->sales->sum('total')),
                'discount_total' => round((float) $shift->sales->sum('discount')),
                'gst_total' => round((float) $shift->sales->sum('gst')),
                'returns_total' => round((float) $returns->sum('finalTotal')),
                'net_total' => round((float) $shift->sales->sum('finalTotal') - (float) $returns->sum('finalTotal')),
                'items' => array_values($itemRows),
            ];
        })->values();

        return response()->json([
            'status' => true,
            'counter' => $counter,
            'date' => today()->toDateString(),
            'shifts' => $reportShifts,
            'totals' => [
                'sales_total' => $reportShifts->sum('sales_total'),
                'gross_total' => $reportShifts->sum('gross_total'),
                'discount_total' => $reportShifts->sum('discount_total'),
                'gst_total' => $reportShifts->sum('gst_total'),
                'returns_total' => $reportShifts->sum('returns_total'),
                'net_total' => $reportShifts->sum('net_total'),
            ],
        ]);
    }
    
    public function generateReportManually()
    {
        Artisan::call('report:daily');

        return response()->json([
            'message' => 'Report generation triggered.',
            'output' => Artisan::output(),
        ]);
    }
}
