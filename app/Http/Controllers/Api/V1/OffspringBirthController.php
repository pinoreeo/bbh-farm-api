<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\BirthEvent;
use App\Models\OffspringBirth;
use App\Models\WeightRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OffspringBirthController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $this->perPage($request);
        $q = OffspringBirth::query()->with(['birthEvent', 'offspringAnimal']);



        if ($request->filled('birth_event_id')) {
            $q->where('birth_event_id', (int) $request->query('birth_event_id'));
        }

        if ($request->filled('offspring_animal_id')) {
            $q->where('offspring_animal_id', (int) $request->query('offspring_animal_id'));
        }

        return response()->json($q->orderByDesc('id')->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, [
            'birth_event_id' => ['required', 'integer', 'exists:breed_births,id'],
            'offspring_animal_id' => ['nullable', 'integer', 'exists:animals,id'],
            'tag_number' => ['nullable', 'string', 'max:100', 'unique:animals,tag_number'],
            'breed_id' => ['required_without:offspring_animal_id', 'integer', 'exists:animal_breeds,id'],
            'sex' => ['required_without:offspring_animal_id', 'in:male,female'],
            'generation' => ['required_without:offspring_animal_id', Rule::in(['F1', 'F2', 'F3', 'F4', 'F5', 'Pure Breed'])],
            'birth_weight_kg' => ['required', 'numeric', 'min:0'],
            'birth_status' => ['nullable', 'in:alive,dead'],
            'notes' => ['nullable', 'string'],
        ]);

        $birthEvent = BirthEvent::find($data['birth_event_id']);
        if (! $birthEvent) {
            return response()->json([
                'message' => 'Data kelahiran yang dipilih tidak ditemukan. Muat ulang halaman lalu pilih data kelahiran yang tersedia.',
            ], 422);
        }

        $animal = isset($data['offspring_animal_id']) ? Animal::find($data['offspring_animal_id']) : null;
        if (isset($data['offspring_animal_id']) && ! $animal) {
            return response()->json(['message' => 'Tag kambing yang dipilih tidak valid atau datanya sudah tidak tersedia.'], 422);
        }

        if ($animal) {
            $alreadyUsed = OffspringBirth::query()
                ->where('offspring_animal_id', $data['offspring_animal_id'])
                ->exists();

            if ($alreadyUsed) {
                return response()->json([
                    'message' => "Kambing dengan tag {$animal->tag_number} sudah tercatat pada data kelahiran lain.",
                ], 422);
            }

            $exists = OffspringBirth::query()
                ->where('birth_event_id', $data['birth_event_id'])
                ->where('offspring_animal_id', $data['offspring_animal_id'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => "Kambing dengan tag {$animal->tag_number} sudah tercatat sebagai anak pada data kelahiran ini.",
                ], 422);
            }

            if ($animal->birth_date && $birthEvent->birth_date && $animal->birth_date->toDateString() !== $birthEvent->birth_date->toDateString()) {
                return response()->json([
                    'message' => "Tanggal lahir kambing {$animal->tag_number} harus sama dengan tanggal pada data kelahiran yang dipilih.",
                ], 422);
            }
        }

        $birthStatus = $data['birth_status'] ?? 'alive';
        $data['birth_status'] = $birthStatus;

        $row = DB::transaction(function () use ($data, $animal, $birthEvent, $birthStatus) {
            if (! $animal) {
                $animal = $this->createOffspringAnimal($data, $birthEvent, $birthStatus);
                $data['offspring_animal_id'] = $animal->id;
            }

            if ($birthStatus === 'dead' && $animal->life_status !== 'dead') {
                $animal->forceFill(['life_status' => 'dead'])->save();
            }

            $offspringBirth = OffspringBirth::create($data);

            $this->syncBirthWeight(
                (int) $data['offspring_animal_id'],
                $birthEvent->birth_date?->toDateString() ?? now()->toDateString(),
                $data['birth_weight_kg'],
            );

            return $offspringBirth;
        });

        return response()->json([
            'message' => 'Data cempe lahir berhasil disimpan.',
            'data' => $row->load(['birthEvent', 'offspringAnimal']),
        ], 201);
    }

    public function show(OffspringBirth $offspringBirth): JsonResponse
    {
        return response()->json($offspringBirth->load(['birthEvent', 'offspringAnimal']));
    }

    public function update(Request $request, OffspringBirth $offspringBirth): JsonResponse
    {
        $data = $this->validated($request, [
            'birth_weight_kg' => ['sometimes', 'numeric', 'min:0'],
            'birth_status' => ['sometimes', 'in:alive,dead'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $offspringBirth) {
            $offspringBirth->fill($data)->save();

            if (($data['birth_status'] ?? null) === 'dead' && $offspringBirth->offspringAnimal?->life_status !== 'dead') {
                $offspringBirth->offspringAnimal->forceFill(['life_status' => 'dead'])->save();
            }

            if (array_key_exists('birth_weight_kg', $data)) {
                $this->syncBirthWeight(
                    (int) $offspringBirth->offspring_animal_id,
                    $offspringBirth->birthEvent?->birth_date?->toDateString() ?? now()->toDateString(),
                    $data['birth_weight_kg'],
                );
            }
        });

        return response()->json([
            'message' => 'Data cempe lahir berhasil diperbarui.',
            'data' => $offspringBirth->load(['birthEvent', 'offspringAnimal']),
        ]);
    }

    private function syncBirthWeight(int $animalId, string $recordDate, float|int|string $weightKg): void
    {
        $record = WeightRecord::query()
            ->where('animal_id', $animalId)
            ->whereDate('record_date', $recordDate)
            ->first();

        $payload = [
            'animal_id' => $animalId,
            'record_date' => $recordDate,
            'weight_kg' => $weightKg,
            'notes' => 'Bobot lahir dari data kelahiran.',
        ];

        if ($record) {
            $record->fill($payload)->save();

            return;
        }

        WeightRecord::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createOffspringAnimal(array $data, BirthEvent $birthEvent, string $birthStatus): Animal
    {
        return Animal::query()->create([
            'tag_number' => $data['tag_number'] ?? $this->nextOffspringTag($birthEvent),
            'breed_id' => $data['breed_id'],
            'sex' => $data['sex'],
            'generation' => $data['generation'],
            'birth_date' => $birthEvent->birth_date?->toDateString(),
            'birth_place' => $birthEvent->birth_place,
            'life_status' => $birthStatus === 'dead' ? 'dead' : 'alive',
            'notes' => $data['notes'] ?? null,
            'is_impor' => false,
        ]);
    }

    private function nextOffspringTag(BirthEvent $birthEvent): string
    {
        $year = $birthEvent->birth_date?->format('Y') ?? now()->format('Y');
        $prefix = "BBH-CMP-{$year}-";
        $next = Animal::query()->where('tag_number', 'like', $prefix.'%')->count() + 1;

        do {
            $tag = $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
            $next++;
        } while (Animal::query()->where('tag_number', $tag)->exists());

        return $tag;
    }
}
