<?php

namespace App\Http\Controllers;

use App\Models\PartyLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PartyLedgerController extends Controller
{
    public function index()
    {
        $ledgers = PartyLedger::with(['customer', 'poFromInvoice', 'poToInvoice'])
            ->latest()
            ->get();

        return response()->json($ledgers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $ledger = PartyLedger::create($this->ledgerData($validator->validated()));

        return response()->json([
            'status' => true,
            'message' => 'Party ledger created successfully',
            'data' => $ledger->load(['customer', 'poFromInvoice', 'poToInvoice']),
        ], 201);
    }

    public function show($id)
    {
        $ledger = PartyLedger::with(['customer', 'poFromInvoice', 'poToInvoice'])->findOrFail($id);

        return response()->json($ledger);
    }

    public function update(Request $request, $id)
    {
        $ledger = PartyLedger::with(['customer', 'poFromInvoice', 'poToInvoice'])->findOrFail($id);

        $validator = Validator::make($request->all(), $this->rules(true));

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $ledger->update($this->ledgerData(array_merge(
            $ledger->only($ledger->getFillable()),
            $validator->validated()
        )));

        return response()->json([
            'status' => true,
            'message' => 'Party ledger updated successfully',
            'data' => $ledger->refresh()->load(['customer', 'poFromInvoice', 'poToInvoice']),
        ]);
    }

    public function destroy($id)
    {
        $ledger = PartyLedger::findOrFail($id);
        $ledger->delete();

        return response()->json([
            'status' => true,
            'message' => 'Party ledger deleted successfully',
        ]);
    }

    private function rules(bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes', 'required'] : ['required'];

        return [
            'customer_id' => [...$required, 'exists:customers,id'],
            'location_from' => ['nullable', 'string', 'max:255'],
            'location_to' => ['nullable', 'string', 'max:255'],
            'billing_date_from' => ['nullable', 'date'],
            'billing_date_to' => ['nullable', 'date'],
            'po_from_invoice_id' => ['nullable', 'exists:invoices,id'],
            'po_to_invoice_id' => ['nullable', 'exists:invoices,id'],
            'port_name' => ['nullable', 'string', 'max:255'],
            'lot_number' => ['nullable', 'string', 'max:255'],
            'vessel_name' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'ledger_date' => ['nullable', 'date'],
            'truck_no' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'rate' => ['nullable', 'numeric', 'min:0'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function ledgerData(array $data): array
    {
        $weight = (float) ($data['weight'] ?? 0);
        $rate = (float) ($data['rate'] ?? 0);

        return [
            'customer_id' => $data['customer_id'],
            'location_from' => $data['location_from'] ?? null,
            'location_to' => $data['location_to'] ?? null,
            'billing_date_from' => $data['billing_date_from'] ?? null,
            'billing_date_to' => $data['billing_date_to'] ?? null,
            'po_from_invoice_id' => $data['po_from_invoice_id'] ?? null,
            'po_to_invoice_id' => $data['po_to_invoice_id'] ?? null,
            'port_name' => $data['port_name'] ?? null,
            'lot_number' => $data['lot_number'] ?? null,
            'vessel_name' => $data['vessel_name'] ?? null,
            'serial_number' => $data['serial_number'] ?? null,
            'ledger_date' => $data['ledger_date'] ?? null,
            'truck_no' => $data['truck_no'] ?? null,
            'weight' => $weight,
            'rate' => $rate,
            'amount' => round((float) ($data['amount'] ?? ($weight * $rate)), 2),
            'name' => $data['name'] ?? null,
        ];
    }
}
