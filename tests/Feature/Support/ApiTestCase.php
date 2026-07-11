<?php

namespace Tests\Feature\Support;

use App\Models\Animal;
use App\Models\BirthEvent;
use App\Models\Breed;
use App\Models\BreedingFemale;
use App\Models\BreedingPeriod;
use App\Models\Certificate;
use App\Models\CertificateType;
use App\Models\ColonyPen;
use App\Models\OffspringBirth;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\BreedSeeder;
use Database\Seeders\CertificateTypeSeeder;
use Database\Seeders\RsaKeySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'bbh.admin.email' => 'admin@farm.com',
            'bbh.admin.password' => 'admin',
            'bbh.admin.name' => 'Rio',
        ]);

        $this->seed([
            AdminUserSeeder::class,
            BreedSeeder::class,
            CertificateTypeSeeder::class,
            RsaKeySeeder::class,
        ]);

        $this->admin = User::query()->where('email', 'admin@farm.com')->firstOrFail();
    }

    protected function actingAsAdmin(): void
    {
        Sanctum::actingAs($this->admin);
    }

    protected function breed(): Breed
    {
        return Breed::query()->firstOrFail();
    }

    protected function certificateType(string $typeCode): CertificateType
    {
        return CertificateType::query()->where('type_code', $typeCode)->firstOrFail();
    }

    protected function createAnimal(array $overrides = []): Animal
    {
        return Animal::query()->create(array_merge([
            'tag_number' => 'TAG-'.uniqid(),
            'breed_id' => $this->breed()->id,
            'sex' => 'female',
            'generation' => 'F1',
            'birth_date' => '2024-01-01',
            'birth_place' => 'BBH Farm',
            'life_status' => 'alive',
            'notes' => null,
            'is_impor' => false,
        ], $overrides));
    }

    protected function createPen(array $overrides = []): ColonyPen
    {
        return ColonyPen::query()->create(array_merge([
            'pen_code' => 'KAWIN-'.uniqid(),
            'colony_type' => 'koloni_kawin',
            'location' => 'Kandang A',
            'capacity' => 20,
        ], $overrides));
    }

    protected function createBreedingPeriod(?Animal $male = null, ?ColonyPen $pen = null, array $overrides = []): BreedingPeriod
    {
        $male ??= $this->createAnimal(['sex' => 'male', 'tag_number' => 'MALE-'.uniqid()]);
        $pen ??= $this->createPen();

        return BreedingPeriod::query()->create(array_merge([
            'colony_pen_id' => $pen->id,
            'period_code' => 'PERIODE-'.uniqid(),
            'start_date' => '2026-05-17',
            'end_date' => null,
            'male_animal_id' => $male->id,
            'status' => 'active',
            'notes' => null,
        ], $overrides));
    }

    protected function createBreedingFemale(?BreedingPeriod $period = null, ?Animal $female = null, array $overrides = []): BreedingFemale
    {
        $period ??= $this->createBreedingPeriod();
        $female ??= $this->createAnimal(['sex' => 'female', 'tag_number' => 'FEMALE-'.uniqid()]);

        return BreedingFemale::query()->create(array_merge([
            'breeding_period_id' => $period->id,
            'female_animal_id' => $female->id,
            'entry_date' => '2026-05-17',
            'exit_date' => null,
            'exit_reason' => null,
        ], $overrides));
    }

    /**
     * @return array{dam: Animal, sire: Animal, offspring: Animal, birthEvent: BirthEvent, offspringBirth: OffspringBirth}
     */
    protected function createBirthWorkflow(): array
    {
        $dam = $this->createAnimal(['sex' => 'female', 'tag_number' => 'DAM-'.uniqid()]);
        $sire = $this->createAnimal(['sex' => 'male', 'tag_number' => 'SIRE-'.uniqid()]);
        $offspring = $this->createAnimal([
            'sex' => 'female',
            'tag_number' => 'KID-'.uniqid(),
            'generation' => 'F2',
            'birth_date' => '2026-05-17',
        ]);

        $birthEvent = BirthEvent::query()->create([
            'dam_id' => $dam->id,
            'sire_id' => $sire->id,
            'birth_date' => '2026-05-17',
            'birth_time' => '08:30:00',
            'offspring_count' => 1,
            'birth_process' => 'normal',
            'dam_grade' => 'F1',
            'birth_place' => 'BBH Farm',
            'notes' => null,
        ]);

        $offspringBirth = OffspringBirth::query()->create([
            'birth_event_id' => $birthEvent->id,
            'offspring_animal_id' => $offspring->id,
            'birth_weight_kg' => 3.2,
            'birth_status' => 'alive',
            'notes' => null,
        ]);

        return compact('dam', 'sire', 'offspring', 'birthEvent', 'offspringBirth');
    }

    protected function issueCertificate(string $typeCode = 'BIBIT_UNGGUL', ?Animal $animal = null, array $overrides = []): Certificate
    {
        if ($typeCode === 'KELAHIRAN' && ! $animal) {
            $workflow = $this->createBirthWorkflow();
            $animal = $workflow['offspring'];
        }

        if ($typeCode === 'KEMATIAN' && ! $animal) {
            $animal = $this->createAnimal(['life_status' => 'dead']);
        }

        $animal ??= $this->createAnimal();

        $payload = array_merge([
            'animal_id' => $animal->id,
            'certificate_type_id' => $this->certificateType($typeCode)->id,
            'auto_sign' => true,
        ], $overrides);

        if ($typeCode === 'KEMATIAN') {
            $payload += [
                'death_date' => '2026-05-17',
                'death_time' => '10:00:00',
                'cause_of_death' => 'Sakit',
            ];
        }

        $response = $this->postJson('/api/v1/certificates', $payload)
            ->assertCreated();

        return Certificate::query()->findOrFail($response->json('data.id'));
    }
}
