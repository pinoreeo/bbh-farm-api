<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\BreedingFemale;
use App\Models\BreedingPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BreedingFemaleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $this->perPage($request);

        $q = BreedingFemale::query()->with(['breedingPeriod', 'femaleAnimal']);



        if ($request->filled('breeding_period_id')) {
            $q->where('breeding_period_id', (int) $request->query('breeding_period_id'));
        }

        if ($request->filled('female_animal_id')) {
            $q->where('female_animal_id', (int) $request->query('female_animal_id'));
        }

        return response()->json($q->orderByDesc('id')->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, [
            'breeding_period_id' => ['required', 'integer', 'exists:breed_periods,id'],
            'female_animal_id' => ['required_without:female_animal_ids', 'integer', 'exists:animals,id'],
            'female_animal_ids' => ['nullable', 'array'],
            'female_animal_ids.*' => ['integer', 'exists:animals,id'],
            'entry_date' => ['required', 'date'],
            'exit_date' => ['nullable', 'date'],
            'exit_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $period = BreedingPeriod::query()->with('colonyPen')->find($data['breeding_period_id']);
        if (! $period || $period->status !== 'active') {
            return response()->json([
                'message' => 'Kode periode harus mengarah ke periode kawin yang masih aktif.',
            ], 422);
        }

        $femaleIds = array_values(array_unique(array_map('intval', $data['female_animal_ids'] ?? [$data['female_animal_id']])));

        if ($period->end_date && $data['entry_date'] > $period->end_date->toDateString()) {
            return response()->json([
                'message' => 'Tanggal masuk tidak boleh melewati tanggal selesai periode kawin.',
            ], 422);
        }

        if (! empty($data['exit_date']) && $period->end_date && $data['exit_date'] > $period->end_date->toDateString()) {
            return response()->json([
                'message' => 'Tanggal keluar tidak boleh melewati tanggal selesai periode kawin.',
            ], 422);
        }

        $capacity = (int) ($period->colonyPen?->capacity ?? 0);
        $activeCount = BreedingFemale::query()
            ->where('breeding_period_id', $period->id)
            ->whereNull('exit_date')
            ->count();

        if ($capacity > 0 && ($activeCount + count($femaleIds)) > $capacity) {
            return response()->json([
                'message' => 'Kapasitas kandang pada periode kawin ini sudah penuh.',
            ], 422);
        }

        foreach ($femaleIds as $femaleId) {
            $femaleAnimal = Animal::find($femaleId);
            if (! $femaleAnimal || $femaleAnimal->sex !== 'female') {
                return response()->json([
                    'message' => 'Tag betina harus mengarah ke kambing betina.',
                ], 422);
            }

            $exists = BreedingFemale::query()
                ->where('breeding_period_id', $data['breeding_period_id'])
                ->where('female_animal_id', $femaleId)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => "Betina dengan tag {$femaleAnimal->tag_number} sudah terdaftar pada periode kawin ini.",
                ], 422);
            }
        }

        $rows = DB::transaction(function () use ($data, $femaleIds) {
            return collect($femaleIds)->map(function (int $femaleId) use ($data) {
                return BreedingFemale::query()->create([
                    'breeding_period_id' => $data['breeding_period_id'],
                    'female_animal_id' => $femaleId,
                    'entry_date' => $data['entry_date'],
                    'exit_date' => $data['exit_date'] ?? null,
                    'exit_reason' => $data['exit_reason'] ?? null,
                ])->load(['breedingPeriod', 'femaleAnimal']);
            })->values();
        });

        return response()->json([
            'message' => count($femaleIds) > 1 ? 'Data betina kawin berhasil disimpan.' : 'Data betina kawin berhasil disimpan.',
            'data' => count($femaleIds) > 1 ? $rows->all() : $rows->first(),
        ], 201);
    }

    public function show(BreedingFemale $breedingFemale): JsonResponse
    {
        return response()->json($breedingFemale->load(['breedingPeriod', 'femaleAnimal']));
    }

    public function update(Request $request, BreedingFemale $breedingFemale): JsonResponse
    {
        $data = $this->validated($request, [
            'entry_date' => ['sometimes', 'date'],
            'exit_date' => ['nullable', 'date'],
            'exit_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $period = $breedingFemale->breedingPeriod;
        $entryDate = $data['entry_date'] ?? $breedingFemale->entry_date?->toDateString();
        $exitDate = $data['exit_date'] ?? $breedingFemale->exit_date?->toDateString();

        if ($period?->end_date && $entryDate && $entryDate > $period->end_date->toDateString()) {
            return response()->json([
                'message' => 'Tanggal masuk tidak boleh melewati tanggal selesai periode kawin.',
            ], 422);
        }

        if ($period?->end_date && $exitDate && $exitDate > $period->end_date->toDateString()) {
            return response()->json([
                'message' => 'Tanggal keluar tidak boleh melewati tanggal selesai periode kawin.',
            ], 422);
        }

        $breedingFemale->fill($data)->save();

        return response()->json([
            'message' => 'Data betina kawin berhasil diperbarui.',
            'data' => $breedingFemale->load(['breedingPeriod', 'femaleAnimal']),
        ]);
    }

}
