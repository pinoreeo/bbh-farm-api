<?php

namespace Tests\Feature\BranchCoverage;

use App\Models\Certificate;
use App\Models\RsaKey;
use Tests\Feature\Support\ApiTestCase;

class CertificateLifecycleExportBranchCoverageTest extends ApiTestCase
{
    public function test_certificate_lifecycle_index_sign_qr_preview_and_pdf_failure_branches(): void
    {
        $this->actingAsAdmin();

        $certificate = $this->issueCertificate('BIBIT_UNGGUL');

        $this->getJson('/api/v1/certificates?status=active&certificate_type_id='.$certificate->certificate_type_id.'&animal_id='.$certificate->animal_id.'&certificate_number='.$certificate->certificate_number)
            ->assertOk()
            ->assertJsonFragment(['certificate_number' => $certificate->certificate_number]);

        $this->getJson('/api/v1/certificates/'.$certificate->id)
            ->assertOk()
            ->assertJsonPath('certificate_number', $certificate->certificate_number);

        $this->putJson('/api/v1/certificates/'.$certificate->id, [
            'issue_place' => 'Purwokerto',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Sertifikat yang sudah diterbitkan tidak dapat diedit. Cabut sertifikat lama dan terbitkan sertifikat baru jika data perlu diperbaiki.');

        $this->postJson('/api/v1/certificates/'.$certificate->id.'/sign')
            ->assertOk()
            ->assertJsonPath('data.status', 'active');

        $this->getJson('/api/v1/certificates/'.$certificate->id.'/qr')
            ->assertOk()
            ->assertJsonStructure(['certificate_id', 'qr_base64', 'url']);

        $certificate->forceFill(['barcode_value' => null])->save();

        $this->getJson('/api/v1/certificates/'.$certificate->id.'/qr')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'QR code belum tersedia untuk sertifikat ini.');

        $birth = $this->issueCertificate('KELAHIRAN');

        $this->getJson('/api/v1/certificates/'.$birth->id.'/qr')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'QR code hanya tersedia untuk Sertifikat Bibit Unggul.');

        $this->getJson('/api/v1/certificates/'.$birth->id.'/preview')
            ->assertOk();

        $birth->forceFill(['birth_event_id' => null])->save();

        $this->getJson('/api/v1/certificates/'.$birth->id.'/pdf')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'File PDF sertifikat belum dapat dibuat. Pastikan data sertifikat sudah lengkap dan konfigurasi PDF benar.');

        $this->postJson('/api/v1/certificates/'.$certificate->id.'/revoke', [
            'reason' => 'Data tidak valid',
        ])->assertOk()
            ->assertJsonPath('data.status', 'revoked');

        $this->postJson('/api/v1/certificates/'.$certificate->id.'/revoke', [
            'reason' => 'Sudah revoked',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Sertifikat ini sudah dicabut.');

        $this->deleteJson('/api/v1/certificates/'.$certificate->id)
            ->assertMethodNotAllowed();

        $this->postJson('/api/v1/certificates/'.$certificate->id.'/unrevoke')
            ->assertOk()
            ->assertJsonPath('data.status', 'active');

        $this->postJson('/api/v1/certificates/'.$certificate->id.'/unrevoke')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Sertifikat ini belum dalam status dicabut.');

        $expired = $this->issueCertificate('BIBIT_UNGGUL');
        $expired->forceFill(['status' => 'expired'])->save();

        $this->postJson('/api/v1/certificates/'.$expired->id.'/revoke', [
            'reason' => 'Expired certificate',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Sertifikat yang sudah kedaluwarsa tidak dapat dicabut.');

        $this->postJson('/api/v1/certificates/'.$expired->id.'/sign')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Hanya sertifikat aktif yang dapat ditandatangani.');

        $unsigned = $this->issueCertificate('BIBIT_UNGGUL', null, ['auto_sign' => false]);
        RsaKey::query()->update(['is_active' => 0]);

        $this->postJson('/api/v1/certificates/'.$unsigned->id.'/sign')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Sertifikat belum dapat ditandatangani. Pastikan RSA Key aktif sudah tersedia dan konfigurasi kunci benar.');

        Certificate::query()->whereKey($expired->id)->update(['status' => 'expired']);

        $this->getJson('/api/v1/certificates?include_inactive=1&status=expired&certificate_number='.$expired->certificate_number)
            ->assertOk()
            ->assertJsonFragment(['certificate_number' => $expired->certificate_number]);
    }
}
