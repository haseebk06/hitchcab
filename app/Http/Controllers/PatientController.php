<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::with(['doctor', 'creator', 'updater'])->latest()->get();

        return response()->json($patients);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $patient = DB::transaction(function () use ($request, $validated) {
            $validated['patient_code'] = $validated['patient_code'] ?? $this->nextPatientCode();
            $validated['token_number'] = $this->nextTokenNumber();
            $validated['created_by'] = $request->user()?->id;
            $validated['updated_by'] = $request->user()?->id;

            return Patient::create($validated);
        });

        return response()->json([
            'message' => 'Patient created successfully',
            'data' => $patient->load(['doctor', 'creator', 'updater']),
        ], 201);
    }

    public function show($id)
    {
        $patient = Patient::with(['doctor', 'creator', 'updater'])->findOrFail($id);

        return response()->json($patient);
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $validated = $request->validate($this->rules($patient->id, true));
        $validated['updated_by'] = $request->user()?->id;

        $patient->update($validated);

        return response()->json([
            'message' => 'Patient updated successfully',
            'data' => $patient->refresh()->load(['doctor', 'creator', 'updater']),
        ]);
    }

    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->delete();

        return response()->json([
            'message' => 'Patient deleted successfully',
        ]);
    }

    public function resetTokenCounter()
    {
        DB::table('patient_token_counters')->updateOrInsert(
            ['id' => 1],
            [
                'current_token' => 0,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Patient token counter reset successfully',
        ]);
    }

    private function rules(?int $patientId = null, bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes', 'required'] : ['required'];

        return [
            'patient_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('patients', 'patient_code')->ignore($patientId),
            ],
            'doctor_id' => [...$required, 'exists:doctors,id'],
            'name' => [...$required, 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date'],
            'age' => ['nullable', 'integer', 'min:0', 'max:255'],
            'cnic' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('patients', 'cnic')->ignore($patientId),
            ],
            'alternate_phone' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('patients', 'email')->ignore($patientId),
            ],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'blood_group' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'occupation' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:255'],
            'allergies' => ['nullable', 'string'],
            'medical_history' => ['nullable', 'string'],
            'current_medications' => ['nullable', 'string'],
            'chronic_diseases' => ['nullable', 'string'],
            'insurance_provider' => ['nullable', 'string', 'max:255'],
            'insurance_policy_number' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'deceased'])],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function nextPatientCode(): string
    {
        $lastPatient = Patient::where('patient_code', 'like', 'PAT-%')
            ->orderByDesc('id')
            ->first();

        $lastNumber = $lastPatient
            ? (int) str_replace('PAT-', '', $lastPatient->patient_code)
            : 0;

        return 'PAT-' . str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
    }

    private function nextTokenNumber(): int
    {
        $counter = DB::table('patient_token_counters')
            ->where('id', 1)
            ->lockForUpdate()
            ->first();

        if (! $counter) {
            DB::table('patient_token_counters')->insert([
                'id' => 1,
                'current_token' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $currentToken = 0;
        } else {
            $currentToken = (int) $counter->current_token;
        }

        $nextToken = $currentToken + 1;

        DB::table('patient_token_counters')
            ->where('id', 1)
            ->update([
                'current_token' => $nextToken,
                'updated_at' => now(),
            ]);

        return $nextToken;
    }
}
