<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with(['creator', 'updater'])->latest()->get();

        return response()->json($doctors);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $validated['doctor_code'] = $validated['doctor_code'] ?? $this->nextDoctorCode();
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        $doctor = Doctor::create($validated);

        return response()->json([
            'message' => 'Doctor created successfully',
            'data' => $doctor->load(['creator', 'updater']),
        ], 201);
    }

    public function show($id)
    {
        $doctor = Doctor::with(['creator', 'updater'])->findOrFail($id);

        return response()->json($doctor);
    }

    public function update(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);

        $validated = $request->validate($this->rules($doctor->id, true));
        $validated['updated_by'] = $request->user()?->id;

        $doctor->update($validated);

        return response()->json([
            'message' => 'Doctor updated successfully',
            'data' => $doctor->refresh()->load(['creator', 'updater']),
        ]);
    }

    public function destroy($id)
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->delete();

        return response()->json([
            'message' => 'Doctor deleted successfully',
        ]);
    }

    private function rules(?int $doctorId = null, bool $isUpdate = false): array
    {
        $required = $isUpdate ? ['sometimes', 'required'] : ['required'];

        return [
            'doctor_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('doctors', 'doctor_code')->ignore($doctorId),
            ],
            'name' => [...$required, 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date'],
            'cnic' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('doctors', 'cnic')->ignore($doctorId),
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('doctors', 'email')->ignore($doctorId),
            ],
            'address' => ['nullable', 'string'],
            'department' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'license_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('doctors', 'license_number')->ignore($doctorId),
            ],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:255'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'available_from' => ['nullable', 'date_format:H:i'],
            'available_to' => ['nullable', 'date_format:H:i'],
            'available_days' => ['nullable', 'array'],
            'available_days.*' => ['string', 'max:20'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'on_leave'])],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function nextDoctorCode(): string
    {
        $lastDoctor = Doctor::where('doctor_code', 'like', 'DOC-%')
            ->orderByDesc('id')
            ->first();

        $lastNumber = $lastDoctor
            ? (int) str_replace('DOC-', '', $lastDoctor->doctor_code)
            : 0;

        return 'DOC-' . str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
    }
}
