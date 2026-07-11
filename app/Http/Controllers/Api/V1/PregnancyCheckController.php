<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\BreedingFemale;
use App\Models\BreedingPeriod;
use App\Models\PregnancyCheck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PregnancyCheckController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $this->perPage($request);

        $q = PregnancyCheck::query()->with([
            'breedingFemale.breedingPeriod.colonyPen',
            'breedingFemale.breedingPeriod.maleAnimal',
            'breedingFemale.femaleAnimal',
            'breedingPeriod.colonyPen',
            'breedingPeriod.maleAnimal',
            'femaleAnimal',
        ]);



        if ($request->filled('breeding_period_id')) {
            $q->where('breeding_period_id', (int) $request->query('breeding_period_id'));
        }

        if ($request->filled('breeding_female_id')) {
            $q->where('breeding_female_id', (int) $request->query('breeding_female_id'));
        }

        if ($request->filled('female_animal_id')) {
            $q->where('female_animal_id', (int) $request->query('female_animal_id'));
        }

        if ($request->filled('is_pregnant')) {
            $q->where('is_pregnant', (int) $request->query('is_pregnant') ? 1 : 0);
        }

        return response()->json($q->orderByDesc('check_date')->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, [
            'breeding_female_id' => ['nullable', 'integer', 'exists:breed_females,id'],
            'breeding_period_id' => ['required_without:breeding_female_id', 'integer', 'exists:breed_periods,id'],
            'female_animal_id' => ['required_without:breeding_female_id', 'integer', 'exists:animals,id'],
            'check_date' => ['required', 'date'],
            'is_pregnant' => ['required', 'boolean'],
            'outcome_status' => ['nullable', 'in:born'],
            'method' => ['nullable', 'string', 'max:100'],
            'estimated_gestation_days' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $context = $this->resolvePregnancyCheckContext($data);
        if ($context instanceof JsonResponse) {
            return $context;
        }

        $breedingFemale = $context;
        $data['breeding_female_id'] = $breedingFemale->id;
        $data['breeding_period_id'] = $breedingFemale->breeding_period_id;
        $data['female_animal_id'] = $breedingFemale->female_animal_id;

        $exists = PregnancyCheck::query()
            ->where('breeding_female_id', $data['breeding_female_id'])
            ->whereDate('check_date', $data['check_date'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Pemeriksaan kebuntingan untuk betina dan tanggal tersebut sudah pernah dicatat.',
            ], 422);
        }

        if (! $data['is_pregnant']) {
            $data['estimated_gestation_days'] = null;
        }

        $row = PregnancyCheck::create($data);

        return response()->json([
            'message' => 'Pemeriksaan kebuntingan berhasil disimpan.',
            'data' => $this->loadRelations($row),
        ], 201);
    }

    public function show(PregnancyCheck $pregnancyCheck): JsonResponse
    {
        return response()->json($this->loadRelations($pregnancyCheck));
    }

    public function update(Request $request, PregnancyCheck $pregnancyCheck): JsonResponse
    {
        $data = $this->validated($request, [
            'breeding_female_id' => ['sometimes', 'integer', 'exists:breed_females,id'],
            'breeding_period_id' => ['sometimes', 'integer', 'exists:breed_periods,id'],
            'female_animal_id' => ['sometimes', 'integer', 'exists:animals,id'],
            'check_date' => ['sometimes', 'date'],
            'is_pregnant' => ['sometimes', 'boolean'],
            'outcome_status' => ['nullable', 'in:born'],
            'method' => ['nullable', 'string', 'max:100'],
            'estimated_gestation_days' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $newPeriodId = $data['breeding_period_id'] ?? $pregnancyCheck->breeding_period_id;
        $newFemaleAnimalId = $data['female_animal_id'] ?? $pregnancyCheck->female_animal_id;
        $newDate = $data['check_date'] ?? $pregnancyCheck->check_date?->format('Y-m-d');

        $contextPayload = [
            'breeding_female_id' => $data['breeding_female_id'] ?? $pregnancyCheck->breeding_female_id,
            'breeding_period_id' => $newPeriodId,
            'female_animal_id' => $newFemaleAnimalId,
            'check_date' => $newDate,
        ];

        $context = $this->resolvePregnancyCheckContext($contextPayload);
        if ($context instanceof JsonResponse) {
            return $context;
        }

        $breedingFemale = $context;
        $data['breeding_female_id'] = $breedingFemale->id;
        $data['breeding_period_id'] = $breedingFemale->breeding_period_id;
        $data['female_animal_id'] = $breedingFemale->female_animal_id;

        $exists = PregnancyCheck::query()
            ->where('id', '!=', $pregnancyCheck->id)
            ->where('breeding_female_id', $breedingFemale->id)
            ->whereDate('check_date', $newDate)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Pemeriksaan kebuntingan lain untuk betina dan tanggal tersebut sudah pernah dicatat.',
            ], 422);
        }

        if (array_key_exists('is_pregnant', $data) && ! $data['is_pregnant']) {
            $data['estimated_gestation_days'] = null;
        }

        $pregnancyCheck->fill($data)->save();

        return response()->json([
            'message' => 'Pemeriksaan kebuntingan berhasil diperbarui.',
            'data' => $this->loadRelations($pregnancyCheck),
        ]);
    }

    private function resolvePregnancyCheckContext(array $data): BreedingFemale|JsonResponse
    {
        $breedingFemale = isset($data['breeding_female_id'])
            ? BreedingFemale::query()->with(['breedingPeriod', 'femaleAnimal'])->find($data['breeding_female_id'])
            : BreedingFemale::query()
                ->with(['breedingPeriod', 'femaleAnimal'])
                ->where('breeding_period_id', $data['breeding_period_id'])
                ->where('female_animal_id', $data['female_animal_id'])
                ->whereNull('exit_date')
                ->first();

        if (! $breedingFemale || $breedingFemale->exit_date !== null) {
            if (! isset($data['breeding_female_id'])) {
                return response()->json([
                    'message' => 'Betina yang dipilih belum terdaftar aktif pada periode kawin tersebut.',
                ], 422);
            }

            return response()->json([
                'message' => 'Data betina dalam periode kawin tidak valid atau sudah tidak aktif.',
            ], 422);
        }

        $period = $breedingFemale->breedingPeriod;
        if (! $period || $period->status !== 'active') {
            return response()->json([
                'message' => 'Kode periode harus mengarah ke periode kawin yang masih aktif.',
            ], 422);
        }

        $femaleAnimal = $breedingFemale->femaleAnimal;
        if (! $femaleAnimal || strtolower((string) $femaleAnimal->sex) !== 'female') {
            return response()->json([
                'message' => 'Tag betina harus mengarah ke kambing betina.',
            ], 422);
        }

        $checkDate = $data['check_date'];

        if ($period->start_date && $checkDate < $period->start_date->toDateString()) {
            return response()->json([
                'message' => 'Tanggal periksa tidak boleh lebih awal dari tanggal mulai periode kawin.',
            ], 422);
        }

        if ($period->end_date && $checkDate > $period->end_date->toDateString()) {
            return response()->json([
                'message' => 'Tanggal periksa tidak boleh melewati tanggal selesai periode kawin.',
            ], 422);
        }

        if ($breedingFemale->entry_date && $checkDate < $breedingFemale->entry_date->toDateString()) {
            return response()->json([
                'message' => 'Tanggal periksa tidak boleh lebih awal dari tanggal masuk betina ke periode kawin.',
            ], 422);
        }

        if ($breedingFemale->exit_date && $checkDate > $breedingFemale->exit_date->toDateString()) {
            return response()->json([
                'message' => 'Tanggal periksa tidak boleh melewati tanggal keluar betina dari periode kawin.',
            ], 422);
        }

        return $breedingFemale;
    }

    private function loadRelations(PregnancyCheck $pregnancyCheck): PregnancyCheck
    {
        return $pregnancyCheck->load([
            'breedingFemale.breedingPeriod.colonyPen',
            'breedingFemale.breedingPeriod.maleAnimal',
            'breedingFemale.femaleAnimal',
            'breedingPeriod.colonyPen',
            'breedingPeriod.maleAnimal',
            'femaleAnimal',
        ]);
    }
}
