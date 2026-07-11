<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnimalController extends Controller
{
    private const GENERATION_OPTIONS = ['F1', 'F2', 'F3', 'F4', 'F5', 'Pure Breed'];

    public function index(Request $request): JsonResponse
    {
        $perPage = $this->perPage($request);
        $q = Animal::query()->with(['breed']);



        if ($request->filled('life_status')) {
            $q->where('life_status', $request->query('life_status'));
        }

        if ($request->filled('sex')) {
            $q->where('sex', $request->query('sex'));
        }

        if ($request->filled('breed_id')) {
            $q->where('breed_id', (int) $request->query('breed_id'));
        }

        if ($request->filled('search')) {
            $s = trim($request->query('search'));
            $q->where('tag_number', 'like', "%{$s}%");
        }

        if ($request->filled('is_impor')) {
            $q->where('is_impor', $request->query('is_impor'));
        }

        return response()->json($q->orderByDesc('id')->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, [
            'tag_number' => ['required', 'string', 'max:100', 'unique:animals,tag_number'],
            'breed_id' => ['required', 'integer', 'exists:animal_breeds,id'],
            'sex' => ['required', 'in:male,female'],
            'generation' => ['required', Rule::in(self::GENERATION_OPTIONS)],
            'birth_date' => ['nullable', 'date'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'life_status' => ['nullable', 'in:alive,dead'],
            'notes' => ['nullable', 'string'],
            'is_impor' => ['required', 'boolean'],
        ]);

        $data['life_status'] = $data['life_status'] ?? 'alive';

        $row = Animal::create($data);

        return response()->json([
            'message' => 'Data kambing berhasil disimpan.',
            'data' => $row->load('breed'),
        ], 201);
    }

    public function show(Animal $animal): JsonResponse
    {
        return response()->json($animal->load(['breed']));
    }

    public function update(Request $request, Animal $animal): JsonResponse
    {
        $data = $this->validated($request, [
            'tag_number' => ['sometimes', 'string', 'max:100', Rule::unique('animals', 'tag_number')->ignore($animal->id)],
            'breed_id' => ['sometimes', 'integer', 'exists:animal_breeds,id'],
            'sex' => ['sometimes', 'in:male,female'],
            'generation' => ['sometimes', Rule::in(self::GENERATION_OPTIONS)],
            'birth_date' => ['nullable', 'date'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'life_status' => ['sometimes', 'in:alive,dead'],
            'notes' => ['nullable', 'string'],
            'is_impor' => ['sometimes', 'boolean'],
        ]);

        $animal->fill($data)->save();

        return response()->json([
            'message' => 'Data kambing berhasil diperbarui.',
            'data' => $animal->load('breed'),
        ]);
    }

}
