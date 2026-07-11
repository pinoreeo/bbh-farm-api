<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ColonyPen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ColonyPenController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $this->perPage($request);
        $q = ColonyPen::query();

        if ($request->filled('colony_type')) {
            $q->where('colony_type', $request->query('colony_type'));
        }

        if ($request->filled('search')) {
            $s = trim($request->query('search'));
            $q->where('pen_code', 'like', "%{$s}%");
        }

        return response()->json(
            $q->orderBy('pen_code')
                ->orderBy('id')
                ->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, [
            'pen_code' => ['required', 'string', 'max:100', 'unique:animal_pens,pen_code'],
            'colony_type' => ['required', 'in:koloni_kawin,koloni_bunting'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:0'],
        ]);

        $row = ColonyPen::create($data);

        return response()->json([
            'message' => 'Data kandang berhasil disimpan.',
            'data' => $row,
        ], 201);
    }

    public function show(ColonyPen $colonyPen): JsonResponse
    {
        return response()->json($colonyPen);
    }

    public function update(Request $request, ColonyPen $colonyPen): JsonResponse
    {
        $data = $this->validated($request, [
            'pen_code' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('animal_pens', 'pen_code')->ignore($colonyPen->id),
            ],
            'colony_type' => ['sometimes', 'in:koloni_kawin,koloni_bunting'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:0'],
        ]);

        $colonyPen->fill($data)->save();

        return response()->json([
            'message' => 'Data kandang berhasil diperbarui.',
            'data' => $colonyPen,
        ]);
    }

}
