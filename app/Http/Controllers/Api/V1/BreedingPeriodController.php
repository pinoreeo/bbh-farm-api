<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\BreedingPeriod;
use App\Models\ColonyPen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BreedingPeriodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $this->perPage($request);

        $q = BreedingPeriod::query()->with(['colonyPen', 'maleAnimal']);

        if ($request->filled('status')) {
            $q->where('status', $request->query('status'));
        } else {
            if (! (int) $request->query('include_closed', 0)) {
                $q->where('status', 'active');
            }
        }

        if ($request->filled('colony_pen_id')) {
            $q->where('colony_pen_id', (int) $request->query('colony_pen_id'));
        }

        if ($request->filled('male_animal_id')) {
            $q->where('male_animal_id', (int) $request->query('male_animal_id'));
        }

        if ($request->filled('search')) {
            $s = trim($request->query('search'));
            $q->where('period_code', 'like', "%{$s}%");
        }

        return response()->json($q->orderByDesc('id')->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, [
            'colony_pen_id' => ['required', 'integer', 'exists:animal_pens,id'],
            'period_code' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date'],
            'male_animal_id' => ['required', 'integer', 'exists:animals,id'],
            'status' => ['nullable', 'in:active,closed'],
            'notes' => ['nullable', 'string'],
        ]);

        $maleAnimal = Animal::find($data['male_animal_id']);
        if (! $maleAnimal || $maleAnimal->sex !== 'male') {
            return response()->json([
                'message' => 'Tag pejantan harus mengarah ke kambing jantan.',
            ], 422);
        }

        $pen = ColonyPen::find($data['colony_pen_id']);
        if (! $pen || $pen->colony_type !== 'koloni_kawin') {
            return response()->json([
                'message' => 'Kode kandang harus mengarah ke kandang perkawinan.',
            ], 422);
        }

        $exists = BreedingPeriod::query()
            ->where('colony_pen_id', $data['colony_pen_id'])
            ->where('period_code', $data['period_code'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Kode periode sudah digunakan pada kandang tersebut.',
            ], 422);
        }

        $data['status'] = $data['status'] ?? 'active';

        $row = BreedingPeriod::create($data);

        return response()->json([
            'message' => 'Periode kawin berhasil disimpan.',
            'data' => $row->load(['colonyPen', 'maleAnimal']),
        ], 201);
    }

    public function show(BreedingPeriod $breedingPeriod): JsonResponse
    {
        return response()->json(
            $breedingPeriod->load(['colonyPen', 'maleAnimal', 'females', 'pregnancyChecks'])
        );
    }

    public function update(Request $request, BreedingPeriod $breedingPeriod): JsonResponse
    {
        $data = $this->validated($request, [
            'colony_pen_id' => ['sometimes', 'integer', 'exists:animal_pens,id'],
            'period_code' => ['sometimes', 'string', 'max:100'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date'],
            'male_animal_id' => ['sometimes', 'integer', 'exists:animals,id'],
            'status' => ['sometimes', 'in:active,closed'],
            'notes' => ['nullable', 'string'],
        ]);

        $newPen = $data['colony_pen_id'] ?? $breedingPeriod->colony_pen_id;
        $newCode = $data['period_code'] ?? $breedingPeriod->period_code;
        $newMaleAnimalId = $data['male_animal_id'] ?? $breedingPeriod->male_animal_id;

        $maleAnimal = Animal::find($newMaleAnimalId);
        if (! $maleAnimal || $maleAnimal->sex !== 'male') {
            return response()->json([
                'message' => 'Tag pejantan harus mengarah ke kambing jantan.',
            ], 422);
        }

        $pen = ColonyPen::find($newPen);
        if (! $pen || $pen->colony_type !== 'koloni_kawin') {
            return response()->json([
                'message' => 'Kode kandang harus mengarah ke kandang perkawinan.',
            ], 422);
        }

        $exists = BreedingPeriod::query()
            ->where('id', '!=', $breedingPeriod->id)
            ->where('colony_pen_id', $newPen)
            ->where('period_code', $newCode)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Kode periode sudah digunakan pada kandang tersebut.',
            ], 422);
        }

        $breedingPeriod->fill($data)->save();

        return response()->json([
            'message' => 'Periode kawin berhasil diperbarui.',
            'data' => $breedingPeriod->load(['colonyPen', 'maleAnimal']),
        ]);
    }

    public function close(BreedingPeriod $breedingPeriod): JsonResponse
    {
        if (($breedingPeriod->status ?? 'active') === 'closed') {
            return response()->json([
                'message' => 'Periode kawin ini sudah ditutup.',
            ], 422);
        }

        $breedingPeriod->status = 'closed';
        $breedingPeriod->save();

        return response()->json([
            'message' => 'Periode kawin berhasil ditutup.',
            'data' => $breedingPeriod->load(['colonyPen', 'maleAnimal']),
        ]);
    }
}
