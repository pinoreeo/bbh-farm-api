<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\BirthEvent;
use App\Models\PregnancyCheck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BirthEventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $this->perPage($request);
        $q = BirthEvent::query()->with(['dam', 'sire']);



        if ($request->filled('dam_id')) {
            $q->where('dam_id', (int) $request->query('dam_id'));
        }

        if ($request->filled('birth_date')) {
            $q->where('birth_date', $request->query('birth_date'));
        }

        return response()->json($q->orderByDesc('birth_date')->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, [
            'dam_id' => ['required', 'integer', 'exists:animals,id'],
            'sire_id' => ['nullable', 'integer', 'exists:animals,id'],
            'birth_date' => ['required', 'date'],
            'birth_time' => ['nullable', 'date_format:H:i:s'],
            'offspring_count' => ['required', 'integer', 'min:1'],
            'birth_process' => ['required', 'string', 'max:100'],
            'dam_grade' => ['nullable', 'string', 'max:50'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $dam = Animal::find($data['dam_id']);
        if (! $dam || $dam->sex !== 'female') {
            return response()->json([
                'message' => 'Tag induk harus mengarah ke kambing betina.',
            ], 422);
        }

        if (! empty($data['sire_id'])) {
            if ((int) $data['sire_id'] === (int) $data['dam_id']) {
                return response()->json([
                    'message' => 'Tag induk dan tag pejantan tidak boleh menggunakan kambing yang sama.',
                ], 422);
            }

            $sire = Animal::find($data['sire_id']);
            if (! $sire || $sire->sex !== 'male') {
                return response()->json([
                    'message' => 'Tag pejantan harus mengarah ke kambing jantan.',
                ], 422);
            }
        }

        $pregnancyCheck = $this->latestPregnantCheck((int) $data['dam_id']);
        if (! $pregnancyCheck) {
            return response()->json([
                'message' => "Kelahiran hanya dapat dicatat untuk induk {$dam->tag_number} yang sudah berstatus bunting dan belum tercatat melahirkan.",
            ], 422);
        }

        $expectedSireId = $pregnancyCheck->breedingPeriod?->male_animal_id;
        if ($expectedSireId !== null) {
            $data['sire_id'] = $expectedSireId;
        }


        $row = DB::transaction(function () use ($data, $pregnancyCheck) {
            $birthEvent = BirthEvent::create($data);

            $pregnancyCheck->forceFill(['outcome_status' => 'born'])->save();

            return $birthEvent;
        });

        return response()->json([
            'message' => 'Data kelahiran berhasil disimpan.',
            'data' => $row->load(['dam', 'sire']),
        ], 201);
    }

    public function show(BirthEvent $birthEvent): JsonResponse
    {
        return response()->json($birthEvent->load(['dam', 'sire', 'offspringBirths', 'postnatalCareRecords']));
    }

    public function update(Request $request, BirthEvent $birthEvent): JsonResponse
    {
        $data = $this->validated($request, [
            'dam_id' => ['sometimes', 'integer', 'exists:animals,id'],
            'sire_id' => ['nullable', 'integer', 'exists:animals,id'],
            'birth_date' => ['sometimes', 'date'],
            'birth_time' => ['nullable', 'date_format:H:i:s'],
            'offspring_count' => ['sometimes', 'integer', 'min:1'],
            'birth_process' => ['sometimes', 'string', 'max:100'],
            'dam_grade' => ['nullable', 'string', 'max:50'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $newDamId = $data['dam_id'] ?? $birthEvent->dam_id;
        $newSireId = array_key_exists('sire_id', $data) ? $data['sire_id'] : $birthEvent->sire_id;

        $dam = Animal::find($newDamId);
        if (! $dam || $dam->sex !== 'female') {
            return response()->json([
                'message' => 'Tag induk harus mengarah ke kambing betina.',
            ], 422);
        }

        $damChanged = array_key_exists('dam_id', $data) && (int) $data['dam_id'] !== (int) $birthEvent->dam_id;
        if ($damChanged) {
            $pregnancyCheck = $this->latestPregnantCheck((int) $newDamId);
            if (! $pregnancyCheck) {
                return response()->json([
                    'message' => "Kelahiran hanya dapat dicatat untuk induk {$dam->tag_number} yang sudah berstatus bunting dan belum tercatat melahirkan.",
                ], 422);
            }

            if ($pregnancyCheck->breedingPeriod?->male_animal_id !== null) {
                $data['sire_id'] = $pregnancyCheck->breedingPeriod->male_animal_id;
                $newSireId = $data['sire_id'];
            }
        } elseif (array_key_exists('sire_id', $data)) {
            unset($data['sire_id']);
            $newSireId = $birthEvent->sire_id;
        }

        if (! empty($newSireId)) {
            if ((int) $newSireId === (int) $newDamId) {
                return response()->json([
                    'message' => 'Tag induk dan tag pejantan tidak boleh menggunakan kambing yang sama.',
                ], 422);
            }

            $sire = Animal::find($newSireId);
            if (! $sire || $sire->sex !== 'male') {
                return response()->json([
                    'message' => 'Tag pejantan harus mengarah ke kambing jantan.',
                ], 422);
            }
        }

        $birthEvent->fill($data)->save();

        return response()->json([
            'message' => 'Data kelahiran berhasil diperbarui.',
            'data' => $birthEvent->load(['dam', 'sire']),
        ]);
    }

    private function latestPregnantCheck(int $damId): ?PregnancyCheck
    {
        return PregnancyCheck::query()
            ->with('breedingPeriod')
            ->where('female_animal_id', $damId)
            ->where('is_pregnant', true)
            ->where(function ($query) {
                $query->whereNull('outcome_status')
                    ->orWhere('outcome_status', '!=', 'born');
            })
            ->latest('check_date')
            ->first();
    }
}
