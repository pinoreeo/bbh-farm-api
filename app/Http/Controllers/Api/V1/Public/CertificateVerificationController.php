<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\CertificatePdfIntegrityService;
use App\Services\CertificateVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CertificateVerificationController extends Controller
{
    private const PUBLIC_RELATIONS = ['certificateType', 'animal'];

    private const VERIFICATION_RELATIONS = ['signature.rsaKey', 'certificateType', 'animal', 'revocation'];

    private const PDF_VERIFICATION_RELATIONS = [
        'signature.rsaKey',
        'officialPdfRsaKey',
        'certificateType',
        'animal',
        'revocation',
    ];

    public function __construct(
        private readonly CertificateVerificationService $verificationService
    ) {}

    public function showPublic(string $certificate_number): JsonResponse
    {
        $cert = Certificate::query()
            ->with(self::PUBLIC_RELATIONS)
            ->where('certificate_number', $certificate_number)
            ->first();

        if (! $cert) {
            return response()->json([
                'message' => 'Sertifikat tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'certificate_number' => $cert->certificate_number,
            'status' => $cert->status,
            'issue_date' => $cert->issue_date,
            'issue_place' => $cert->issue_place,
            ...$this->verificationService->publicCertificateData($cert),
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $this->validated($request, [
            'certificate_number' => ['required', 'string', 'max:150'],
        ]);

        $cert = Certificate::query()
            ->with(self::VERIFICATION_RELATIONS)
            ->where('certificate_number', $data['certificate_number'])
            ->first();

        if (! $cert) {
            return response()->json([
                'message' => 'Nomor sertifikat tidak ditemukan.',
                'is_valid' => false,
            ], 404);
        }

        return response()->json($this->verificationService->verify($cert, 'certificate_number'));
    }

    public function verifyByToken(string $token): JsonResponse
    {
        $cert = Certificate::query()
            ->with(self::VERIFICATION_RELATIONS)
            ->where('verification_token', $token)
            ->first();

        if (! $cert) {
            return response()->json([
                'message' => 'QR code tidak mengarah ke sertifikat yang terdaftar.',
                'is_valid' => false,
            ], 404);
        }

        return response()->json($this->verificationService->verify($cert, 'qr_code'));
    }

    public function verifyPdf(Request $request, CertificatePdfIntegrityService $pdfIntegrity): JsonResponse
    {
        $data = $this->validated($request, [
            'pdf' => ['required', 'file', 'mimetypes:application/pdf', 'mimes:pdf', 'max:10240'],
            'certificate_number' => ['nullable', 'string', 'max:150'],
        ]);

        $startedAt = microtime(true);
        $originalName = $data['pdf']->getClientOriginalName();

        Log::info("PDF 1/1 diterima: {$originalName}");

        $uploadedPath = $data['pdf']->getRealPath();
        if (! $uploadedPath) {
            Log::warning('PDF 1/1 gagal dibaca dari temporary upload path.');

            return response()->json([
                'message' => 'File PDF yang diunggah belum dapat dibaca.',
                'is_valid' => false,
            ], 422);
        }

        Log::info("PDF 1/1 temporary path: {$uploadedPath}");
        Log::info('PDF 1/1 menghitung SHA-256 dokumen...');

        $uploadedHash = hash_file('sha256', $uploadedPath);
        if (! is_string($uploadedHash)) {
            Log::warning('PDF 1/1 SHA-256 gagal dihitung.');

            return response()->json([
                'message' => 'File PDF belum dapat diverifikasi karena nilai hash dokumen tidak dapat dihitung.',
                'is_valid' => false,
            ], 422);
        }

        Log::info('PDF 1/1 SHA-256: '.substr($uploadedHash, 0, 16).'...');

        $certQuery = Certificate::query()
            ->with(self::PDF_VERIFICATION_RELATIONS);

        if (! empty($data['certificate_number'])) {
            Log::info('PDF 1/1 mencari sertifikat resmi: '.$data['certificate_number']);
            $certQuery->where('certificate_number', $data['certificate_number']);
        } else {
            Log::info('PDF 1/1 mencari sertifikat resmi berdasarkan hash PDF...');
            $certQuery->where('official_pdf_hash_sha256', $uploadedHash);
        }

        $cert = $certQuery->first();

        if (! $cert) {
            $durationMs = number_format((microtime(true) - $startedAt) * 1000, 1);
            Log::info("PDF 1/1 hasil akhir: TIDAK VALID, sertifikat tidak ditemukan, {$durationMs}ms");

            return response()->json([
                'certificate_number' => $data['certificate_number'] ?? null,
                'uploaded_pdf_hash_sha256' => $uploadedHash,
                'official_pdf_hash_sha256' => null,
                'is_pdf_integrity_valid' => false,
                'is_valid' => false,
                'reason' => ! empty($data['certificate_number'])
                    ? 'Nomor sertifikat tidak ditemukan.'
                    : 'File PDF tidak cocok dengan dokumen resmi yang tersimpan di sistem.',
            ]);
        }

        $pdfHashMatches = $cert->official_pdf_hash_sha256
            ? hash_equals((string) $cert->official_pdf_hash_sha256, $uploadedHash)
            : false;

        Log::info('PDF 1/1 membandingkan hash upload dengan hash resmi: '.($pdfHashMatches ? 'cocok' : 'tidak cocok'));
        Log::info('PDF 1/1 memverifikasi tanda tangan digital RSA-SHA256...');

        $pdfSignatureValid = $pdfIntegrity->verifyOfficialPdfSignature($cert);
        $pdfIntegrityValid = $pdfHashMatches && $pdfSignatureValid;
        $certificateVerification = $this->verificationService->verify($cert, 'upload_pdf');

        Log::info('PDF 1/1 nomor sertifikat: '.$cert->certificate_number);
        Log::info('PDF 1/1 signature RSA-SHA256: '.($pdfSignatureValid ? 'valid' : 'tidak valid'));
        Log::info('PDF 1/1 hasil verifikasi tersimpan ke tabel cert_log');

        $reason = null;
        if (! $cert->official_pdf_hash_sha256 || ! $cert->official_pdf_signature_base64 || ! $cert->officialPdfRsaKey) {
            $reason = 'Data keaslian PDF resmi belum tersedia. Unduh PDF resmi terlebih dahulu dari sistem.';
        } elseif (! $pdfHashMatches) {
            $reason = 'File PDF sudah berubah atau bukan dokumen resmi yang diterbitkan sistem.';
        } elseif (! $pdfSignatureValid) {
            $reason = 'Tanda tangan digital PDF tidak valid.';
        } elseif (! $certificateVerification['is_valid']) {
            $reason = 'Data atau status sertifikat tidak valid.';
        }

        $isValid = $pdfIntegrityValid && (bool) $certificateVerification['is_valid'];
        $durationMs = number_format((microtime(true) - $startedAt) * 1000, 1);
        Log::info('PDF 1/1 hasil akhir: '.($isValid ? 'VALID' : 'TIDAK VALID').($reason ? ' - '.$reason : '').", {$durationMs}ms");

        return response()->json([
            'certificate_number' => $cert->certificate_number,
            'verification_token' => $cert->verification_token,
            'uploaded_pdf_hash_sha256' => $uploadedHash,
            'official_pdf_hash_sha256' => $cert->official_pdf_hash_sha256,
            'pdf_hash_matches' => $pdfHashMatches,
            'pdf_signature_valid' => $pdfSignatureValid,
            'is_pdf_integrity_valid' => $pdfIntegrityValid,
            'is_certificate_data_valid' => (bool) $certificateVerification['is_valid'],
            'is_valid' => $isValid,
            'reason' => $reason,
            'certificate_verification' => $certificateVerification,
        ]);
    }
}
