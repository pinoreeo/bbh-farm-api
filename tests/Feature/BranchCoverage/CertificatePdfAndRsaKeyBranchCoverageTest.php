<?php

namespace Tests\Feature\BranchCoverage;

use App\Models\RsaKey;
use App\Models\User;
use App\Services\CertificateSigningService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Support\ApiTestCase;

class CertificatePdfAndRsaKeyBranchCoverageTest extends ApiTestCase
{
    public function test_pdf_verification_covers_lookup_integrity_signature_and_status_branches(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/v1/public/certificates/verify-pdf', [])
            ->assertUnprocessable();

        $this->postJson('/api/v1/public/certificates/verify-pdf', [
            'pdf' => UploadedFile::fake()->create('not-pdf.txt', 1, 'text/plain'),
        ])->assertUnprocessable();

        $this->postJson('/api/v1/public/certificates/verify-pdf', [
            'pdf' => UploadedFile::fake()->createWithContent('unknown.pdf', "%PDF-1.4\nunknown\n%%EOF"),
        ])->assertOk()
            ->assertJsonPath('is_valid', false)
            ->assertJsonPath('reason', 'File PDF tidak cocok dengan dokumen resmi yang tersimpan di sistem.');

        $certificate = $this->issueCertificate('BIBIT_UNGGUL');
        $pdfBytes = "%PDF-1.4\nOfficial certificate\n%%EOF";
        $pdfHash = hash('sha256', $pdfBytes);
        $signedHash = app(CertificateSigningService::class)->signHash($pdfHash);

        $certificate->forceFill([
            'official_pdf_path' => 'certificates/official/testing/certificate.pdf',
            'official_pdf_hash_sha256' => $pdfHash,
            'official_pdf_signature_base64' => $signedHash['signature_base64'],
            'official_pdf_signature_scheme' => config('bbh_signing.signature_scheme', 'RSA-SHA256'),
            'official_pdf_rsa_key_id' => $signedHash['rsa_key']->id,
            'official_pdf_signed_at' => now(),
            'official_pdf_generated_at' => now(),
        ])->save();

        $this->postJson('/api/v1/public/certificates/verify-pdf', [
            'pdf' => UploadedFile::fake()->createWithContent('certificate.pdf', $pdfBytes),
            'certificate_number' => $certificate->certificate_number,
        ])->assertOk()
            ->assertJsonPath('pdf_hash_matches', true)
            ->assertJsonPath('pdf_signature_valid', true)
            ->assertJsonPath('is_pdf_integrity_valid', true)
            ->assertJsonPath('is_certificate_data_valid', true)
            ->assertJsonPath('is_valid', true);

        $this->postJson('/api/v1/public/certificates/verify-pdf', [
            'pdf' => UploadedFile::fake()->createWithContent('certificate.pdf', $pdfBytes),
        ])->assertOk()
            ->assertJsonPath('certificate_number', $certificate->certificate_number)
            ->assertJsonPath('is_valid', true);

        $this->postJson('/api/v1/public/certificates/verify-pdf', [
            'pdf' => UploadedFile::fake()->createWithContent('tampered.pdf', "%PDF-1.4\ntampered\n%%EOF"),
            'certificate_number' => $certificate->certificate_number,
        ])->assertOk()
            ->assertJsonPath('pdf_hash_matches', false)
            ->assertJsonPath('reason', 'File PDF sudah berubah atau bukan dokumen resmi yang diterbitkan sistem.')
            ->assertJsonPath('is_valid', false);

        $certificate->forceFill(['official_pdf_signature_base64' => base64_encode('wrong-signature')])->save();

        $this->postJson('/api/v1/public/certificates/verify-pdf', [
            'pdf' => UploadedFile::fake()->createWithContent('certificate.pdf', $pdfBytes),
            'certificate_number' => $certificate->certificate_number,
        ])->assertOk()
            ->assertJsonPath('pdf_hash_matches', true)
            ->assertJsonPath('pdf_signature_valid', false)
            ->assertJsonPath('reason', 'Tanda tangan digital PDF tidak valid.')
            ->assertJsonPath('is_valid', false);

        $certificate->forceFill([
            'official_pdf_signature_base64' => $signedHash['signature_base64'],
            'official_pdf_hash_sha256' => null,
        ])->save();

        $this->postJson('/api/v1/public/certificates/verify-pdf', [
            'pdf' => UploadedFile::fake()->createWithContent('certificate.pdf', $pdfBytes),
            'certificate_number' => $certificate->certificate_number,
        ])->assertOk()
            ->assertJsonPath('reason', 'Data keaslian PDF resmi belum tersedia. Unduh PDF resmi terlebih dahulu dari sistem.')
            ->assertJsonPath('is_valid', false);

        $certificate->forceFill([
            'official_pdf_hash_sha256' => $pdfHash,
            'status' => 'revoked',
        ])->save();

        $this->postJson('/api/v1/public/certificates/verify-pdf', [
            'pdf' => UploadedFile::fake()->createWithContent('certificate.pdf', $pdfBytes),
            'certificate_number' => $certificate->certificate_number,
        ])->assertOk()
            ->assertJsonPath('is_pdf_integrity_valid', true)
            ->assertJsonPath('is_certificate_data_valid', false)
            ->assertJsonPath('reason', 'Data atau status sertifikat tidak valid.')
            ->assertJsonPath('is_valid', false);
    }

    public function test_rsa_key_management_covers_store_generate_update_activation_and_guard_branches(): void
    {
        $this->actingAsAdmin();
        RsaKey::query()->where('user_id', $this->admin->id)->delete();

        $this->getJson('/api/v1/rsa-keys?is_active=1&search=BBH')
            ->assertOk();

        $this->postJson('/api/v1/rsa-keys', [
            'key_identifier' => 'INVALID-LENGTH',
            'public_key_pem' => $this->publicKeyPem(),
            'key_length' => 4096,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('key_length');

        $this->postJson('/api/v1/rsa-keys', [
            'key_identifier' => 'INVALID-PEM',
            'public_key_pem' => 'not-a-public-key',
            'key_length' => 2048,
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Format public key RSA tidak valid.');

        $publicKeyPem = $this->publicKeyPem();

        $inactive = $this->postJson('/api/v1/rsa-keys', [
            'key_identifier' => 'VALID-INACTIVE-RSA',
            'public_key_pem' => $publicKeyPem,
            'key_length' => 2048,
            'is_active' => false,
        ])->assertCreated()
            ->assertJsonPath('data.is_active', false);

        $this->getJson('/api/v1/rsa-keys?include_inactive=1&search=VALID-INACTIVE')
            ->assertOk()
            ->assertJsonFragment(['key_identifier' => 'VALID-INACTIVE-RSA']);

        $this->postJson('/api/v1/rsa-keys', [
            'key_identifier' => 'DUPLICATE-FINGERPRINT-RSA',
            'public_key_pem' => $publicKeyPem,
            'key_length' => 2048,
            'is_active' => false,
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'User ini sudah memiliki RSA Key.');

        $this->getJson('/api/v1/rsa-keys/'.$inactive->json('data.id'))
            ->assertOk()
            ->assertJsonPath('key_identifier', 'VALID-INACTIVE-RSA');

        $this->postJson('/api/v1/rsa-keys/generate', [
            'key_identifier' => 'INVALID-GENERATE-LENGTH',
            'key_length' => 4096,
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'User ini sudah memiliki RSA Key.');

        $generatedId = (int) $inactive->json('data.id');

        $this->putJson('/api/v1/rsa-keys/'.$generatedId, [
            'public_key_pem' => 'invalid-public-key',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Format public key RSA tidak valid.');

        $this->putJson('/api/v1/rsa-keys/'.$generatedId, [
            'key_length' => 4096,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('key_length');

        $secondActive = RsaKey::query()->create([
            'user_id' => $this->otherAdmin('second-active')->id,
            'key_identifier' => 'SECOND-ACTIVE-RSA',
            'public_key_pem' => $this->publicKeyPem(),
            'algorithm' => 'RSA',
            'key_length' => 2048,
            'fingerprint_sha256' => hash('sha256', 'second-active-rsa'),
            'is_active' => 1,
        ]);

        $this->putJson('/api/v1/rsa-keys/'.$secondActive->id, [
            'is_active' => false,
        ])->assertNotFound();

        $this->postJson('/api/v1/rsa-keys/'.$secondActive->id.'/activate')
            ->assertNotFound();

        $this->postJson('/api/v1/rsa-keys/'.$secondActive->id.'/deactivate')
            ->assertNotFound();

        $extraActive = RsaKey::query()->create([
            'user_id' => $this->otherAdmin('extra-active')->id,
            'key_identifier' => 'EXTRA-ACTIVE-RSA',
            'public_key_pem' => $this->publicKeyPem(),
            'algorithm' => 'RSA',
            'key_length' => 2048,
            'fingerprint_sha256' => hash('sha256', 'extra-active-rsa'),
            'is_active' => 1,
        ]);

        $this->postJson('/api/v1/rsa-keys/'.$extraActive->id.'/deactivate')
            ->assertNotFound();

        $this->postJson('/api/v1/rsa-keys/'.$extraActive->id.'/deactivate')
            ->assertNotFound();

        $this->postJson('/api/v1/rsa-keys/'.$extraActive->id.'/deactivate')
            ->assertNotFound();
    }

    private function publicKeyPem(): string
    {
        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ] + $this->opensslConfigArgs());

        $details = openssl_pkey_get_details($resource);

        return trim((string) $details['key']);
    }

    private function otherAdmin(string $prefix): User
    {
        return User::query()->create([
            'name' => 'Other '.$prefix,
            'email' => $prefix.'@example.test',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function opensslConfigArgs(): array
    {
        $configPath = config('bbh.openssl_conf');

        if (is_string($configPath) && $configPath !== '' && is_file($configPath)) {
            return ['config' => $configPath];
        }

        return [];
    }
}
