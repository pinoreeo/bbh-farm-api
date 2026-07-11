<?php

namespace Tests\Feature\BranchCoverage;

use App\Models\CertificateSignature;
use Tests\Feature\Support\ApiTestCase;

class CertificateIssuanceRsaSha256BranchCoverageTest extends ApiTestCase
{
    public function test_certificate_issuance_covers_type_rules_payload_hash_and_auto_sign_branches(): void
    {
        $this->actingAsAdmin();

        $inactiveType = $this->certificateType('BIBIT_UNGGUL');
        $inactiveType->forceFill(['is_active' => false])->save();

        $this->postJson('/api/v1/certificates', [
            'animal_id' => $this->createAnimal()->id,
            'certificate_type_id' => $inactiveType->id,
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Jenis sertifikat ini sedang tidak aktif.');

        $inactiveType->forceFill(['is_active' => true])->save();

        $unsignedBibit = $this->postJson('/api/v1/certificates', [
            'animal_id' => $this->createAnimal()->id,
            'certificate_type_id' => $this->certificateType('BIBIT_UNGGUL')->id,
            'auto_sign' => false,
        ])->assertCreated()
            ->assertJsonPath('data.certificate_type.type_code', 'BIBIT_UNGGUL')
            ->assertJsonPath('data.barcode_format', 'qrcode');

        $this->assertSame(
            hash('sha256', (string) $unsignedBibit->json('data.payload_snapshot')),
            $unsignedBibit->json('data.hash_sha256')
        );

        $this->assertDatabaseMissing(CertificateSignature::class, [
            'certificate_id' => $unsignedBibit->json('data.id'),
            'status' => 'active',
        ]);

        $signedBibit = $this->postJson('/api/v1/certificates', [
            'animal_id' => $this->createAnimal()->id,
            'certificate_type_id' => $this->certificateType('BIBIT_UNGGUL')->id,
            'auto_sign' => true,
        ])->assertCreated()
            ->assertJsonPath('data.certificate_type.type_code', 'BIBIT_UNGGUL')
            ->assertJsonPath('data.signature.status', 'active');

        $this->assertMatchesRegularExpression('/^BBH-SBU-\d{4}-\d{4}$/', (string) $signedBibit->json('data.certificate_number'));

        $this->postJson('/api/v1/certificates', [
            'animal_id' => $this->createAnimal(['is_impor' => true])->id,
            'certificate_type_id' => $this->certificateType('KELAHIRAN')->id,
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Akta kelahiran hanya dapat diterbitkan untuk hewan yang lahir di kandang.');

        $localBornAnimalWithoutBirthEvent = $this->createAnimal(['is_impor' => false]);

        $this->postJson('/api/v1/certificates', [
            'animal_id' => $localBornAnimalWithoutBirthEvent->id,
            'certificate_type_id' => $this->certificateType('KELAHIRAN')->id,
        ])->assertUnprocessable()
            ->assertJsonPath('message', "Hewan dengan tag {$localBornAnimalWithoutBirthEvent->tag_number} tidak memiliki kejadian kelahiran di peternakan.");

        $workflow = $this->createBirthWorkflow();

        $birthCertificate = $this->postJson('/api/v1/certificates', [
            'animal_id' => $workflow['offspring']->id,
            'certificate_type_id' => $this->certificateType('KELAHIRAN')->id,
            'auto_sign' => false,
        ])->assertCreated()
            ->assertJsonPath('data.certificate_type.type_code', 'KELAHIRAN');

        $this->assertMatchesRegularExpression('/^BBH-AKL-\d{4}-\d{4}$/', (string) $birthCertificate->json('data.certificate_number'));

        $this->postJson('/api/v1/certificates', [
            'animal_id' => $workflow['offspring']->id,
            'certificate_type_id' => $this->certificateType('KELAHIRAN')->id,
            'auto_sign' => false,
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Sertifikat jenis ini sudah pernah diterbitkan untuk hewan tersebut.');

        $this->postJson('/api/v1/certificates', [
            'animal_id' => $this->createAnimal(['life_status' => 'alive'])->id,
            'certificate_type_id' => $this->certificateType('KEMATIAN')->id,
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Akta kematian hanya dapat diterbitkan untuk kambing yang berstatus mati.');

        $deadAnimal = $this->createAnimal(['life_status' => 'dead']);

        $this->postJson('/api/v1/certificates', [
            'animal_id' => $deadAnimal->id,
            'certificate_type_id' => $this->certificateType('KEMATIAN')->id,
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Tanggal kematian wajib diisi untuk sertifikat kematian.');

        $this->postJson('/api/v1/certificates', [
            'animal_id' => $deadAnimal->id,
            'certificate_type_id' => $this->certificateType('KEMATIAN')->id,
            'death_date' => '2026-05-17',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Jam kematian wajib diisi untuk sertifikat kematian.');

        $this->postJson('/api/v1/certificates', [
            'animal_id' => $deadAnimal->id,
            'certificate_type_id' => $this->certificateType('KEMATIAN')->id,
            'death_date' => '2026-05-17',
            'death_time' => '10:00:00',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Penyebab kematian wajib diisi untuk sertifikat kematian.');

        $deathCertificate = $this->postJson('/api/v1/certificates', [
            'animal_id' => $deadAnimal->id,
            'certificate_type_id' => $this->certificateType('KEMATIAN')->id,
            'death_date' => '2026-05-17',
            'death_time' => '10:00:00',
            'cause_of_death' => 'Sakit',
            'auto_sign' => false,
        ])->assertCreated()
            ->assertJsonPath('data.certificate_type.type_code', 'KEMATIAN');

        $this->assertMatchesRegularExpression('/^BBH-AKM-\d{4}-\d{4}$/', (string) $deathCertificate->json('data.certificate_number'));
    }
}
