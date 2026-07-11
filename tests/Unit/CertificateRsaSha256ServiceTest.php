<?php

namespace Tests\Unit;

use App\Services\CertificatePdfIntegrityService;
use App\Services\CertificateSigningService;
use Tests\Feature\Support\ApiTestCase;

class CertificateRsaSha256ServiceTest extends ApiTestCase
{
    public function test_rsa_sha256_signing_service_covers_success_and_failure_branches(): void
    {
        $this->actingAsAdmin();

        $service = app(CertificateSigningService::class);

        $hash = hash('sha256', 'branch-unit-rsa-sha256');
        $signed = $service->signHash($hash);

        $this->assertTrue($service->verifyHash(
            $hash,
            $signed['signature_base64'],
            $signed['rsa_key']->public_key_pem
        ));

        $this->assertFalse($service->verifyHash(
            hash('sha256', 'different-hash'),
            $signed['signature_base64'],
            $signed['rsa_key']->public_key_pem
        ));

        $this->assertFalse($service->verifyHash($hash, 'not valid base64 %%', $signed['rsa_key']->public_key_pem));
        $this->assertFalse($service->verifyHash($hash, $signed['signature_base64'], 'not-a-public-key'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Hash to sign is empty.');
        $service->signHash('');
    }

    public function test_pdf_integrity_service_unit_branches_do_not_require_browser_rendering(): void
    {
        $this->actingAsAdmin();

        $service = app(CertificatePdfIntegrityService::class);
        $bibit = $this->issueCertificate('BIBIT_UNGGUL');
        $birth = $this->issueCertificate('KELAHIRAN');
        $death = $this->issueCertificate('KEMATIAN');

        $this->assertSame('certificates.pdf_sertifikat_hewan', $service->bladeViewFor($bibit));
        $this->assertSame('certificates.pdf_akte_kelahiran', $service->bladeViewFor($birth));
        $this->assertSame('certificates.pdf_akte_kematian', $service->bladeViewFor($death));

        $this->assertSame([0, 0, 487.5, 675], $service->paperFor($bibit));
        $this->assertSame([0, 0, 525, 780], $service->paperFor($birth));
        $this->assertSame([0, 0, 525, 780], $service->paperFor($death));

        $this->assertStringStartsWith('Sertifikat-Bibit-Unggul_', $service->downloadFilenameFor($bibit, [
            'issue_date' => '17/05/2026',
        ]));
        $this->assertFalse($service->verifyOfficialPdfSignature($bibit));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Official PDF has not been generated.');
        $service->officialPdfAbsolutePath($bibit);
    }
}
