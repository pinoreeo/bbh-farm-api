<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\BirthEvent;
use App\Models\Certificate;
use App\Models\CertificateRevocation;
use App\Models\CertificateType;
use App\Services\CertificatePdfIntegrityService;
use App\Services\CertificateSigningService;
use App\Services\CertificateViewDataService;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $this->perPage($request);

        $q = Certificate::query()->with([
            'animal.breed',
            'certificateType',
            'birthEvent',
            'signature.rsaKey',
            'revocation',
        ]);

        if ($request->filled('status')) {
            $q->where('status', $request->query('status'));
        } else {
            if (! (int) $request->query('include_inactive', 0)) {
                $q->where('status', 'active');
            }
        }

        if ($request->filled('certificate_type_id')) {
            $q->where('certificate_type_id', (int) $request->query('certificate_type_id'));
        }

        if ($request->filled('animal_id')) {
            $q->where('animal_id', (int) $request->query('animal_id'));
        }

        if ($request->filled('certificate_number')) {
            $q->where('certificate_number', $request->query('certificate_number'));
        }

        return response()->json(
            $q->orderByDesc('id')->paginate($perPage)
        );
    }

    public function store(Request $request, CertificateSigningService $signingService): JsonResponse
    {
        $data = $this->validated($request, [
            'animal_id' => ['required', 'integer', 'exists:animals,id'],
            'certificate_type_id' => ['required', 'integer', 'exists:cert_types,id'],
            'issue_place' => ['nullable', 'string', 'max:255'],
            'auto_sign' => ['nullable', 'boolean'],
            'death_date' => ['nullable', 'date'],
            'death_time' => ['nullable', 'date_format:H:i:s'],
            'cause_of_death' => ['nullable', 'string', 'max:255'],
        ]);

        $autoSign = array_key_exists('auto_sign', $data) ? (bool) $data['auto_sign'] : true;
        unset($data['auto_sign']);

        return DB::transaction(function () use ($data, $autoSign, $signingService) {
            $animal = Animal::query()->with(['breed'])->findOrFail($data['animal_id']);
            $type = CertificateType::query()
                ->whereKey($data['certificate_type_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! $type->is_active) {
                return response()->json([
                    'message' => 'Jenis sertifikat ini sedang tidak aktif.',
                ], 422);
            }

            $existingTypeCertificate = Certificate::query()
                ->where('animal_id', $animal->id)
                ->where('certificate_type_id', $type->id)
                ->exists();

            if (in_array($type->type_code, ['KELAHIRAN', 'KEMATIAN']) && $existingTypeCertificate) {
                return response()->json([
                    'message' => 'Sertifikat jenis ini sudah pernah diterbitkan untuk hewan tersebut.',
                ], 422);
            }

            $issueDate = now()->toDateString();

            $certificateData = [
                'animal_id' => $animal->id,
                'certificate_type_id' => $type->id,
                'certificate_number' => $this->generateCertificateNumber($type->type_code, $issueDate),
                'verification_token' => (string) Str::uuid(),
                'issue_date' => $issueDate,
                'issue_place' => $data['issue_place'] ?? 'Ajibarang',
                'birth_event_id' => null,
                'valid_from' => $issueDate,
                'valid_until' => null,
                'death_date' => null,
                'death_time' => null,
                'cause_of_death' => null,
                'canonical_method' => 'canonical-json',
                'status' => 'active',
            ];

            switch ($type->type_code) {
                case 'KELAHIRAN':
                    if ((bool) $animal->is_impor) {
                        return response()->json([
                            'message' => 'Akta kelahiran hanya dapat diterbitkan untuk hewan yang lahir di kandang.',
                        ], 422);
                    }

                    $birthEvent = BirthEvent::query()
                        ->whereHas('offspringBirths', function ($q) use ($animal) {
                            $q->where('offspring_animal_id', $animal->id);
                        })
                        ->latest('birth_date')
                        ->first();

                    if (! $birthEvent) {
                        return response()->json([
                            'message' => "Hewan dengan tag {$animal->tag_number} tidak memiliki kejadian kelahiran di peternakan.",
                        ], 422);
                    }

                    $certificateData['birth_event_id'] = $birthEvent->id;
                    $certificateData['valid_from'] = optional($animal->birth_date)->toDateString() ?? $issueDate;
                    break;

                case 'KEMATIAN':
                    if ($animal->life_status !== 'dead') {
                        return response()->json([
                            'message' => 'Akta kematian hanya dapat diterbitkan untuk kambing yang berstatus mati.',
                        ], 422);
                    }

                    if (empty($data['death_date'])) {
                        return response()->json([
                            'message' => 'Tanggal kematian wajib diisi untuk sertifikat kematian.',
                        ], 422);
                    }

                    if (empty($data['death_time'])) {
                        return response()->json([
                            'message' => 'Jam kematian wajib diisi untuk sertifikat kematian.',
                        ], 422);
                    }

                    if (empty($data['cause_of_death'])) {
                        return response()->json([
                            'message' => 'Penyebab kematian wajib diisi untuk sertifikat kematian.',
                        ], 422);
                    }

                    $certificateData['death_date'] = $data['death_date'];
                    $certificateData['death_time'] = $data['death_time'] ?? null;
                    $certificateData['cause_of_death'] = $data['cause_of_death'];
                    break;

                case 'BIBIT_UNGGUL':
                    $certificateData['valid_from'] = $issueDate;
                    break;

                default:
                    return response()->json([
                        'message' => 'Jenis sertifikat tidak dikenali.',
                    ], 422);
            }

            if ($type->type_code === 'BIBIT_UNGGUL') {
                $certificateData['barcode_value'] = url('/api/v1/public/certificates/verify/'.$certificateData['verification_token']);
                $certificateData['barcode_format'] = 'qrcode';
            }

            $payloadSnapshot = $this->buildPayloadSnapshot($certificateData);
            $certificateData['payload_snapshot'] = $payloadSnapshot;
            $certificateData['hash_sha256'] = hash('sha256', $payloadSnapshot);

            $row = Certificate::create($certificateData);

            if ($autoSign && $row->status === 'active') {
                $signingService->sign($row);
            }

            return response()->json([
                'message' => 'Sertifikat berhasil diterbitkan.',
                'data' => $row->load([
                    'animal.breed',
                    'certificateType',
                    'birthEvent',
                    'signature.rsaKey',
                ]),
            ], 201);
        });
    }

    public function show(Certificate $certificate): JsonResponse
    {
        return response()->json(
            $certificate->load([
                'animal.breed',
                'certificateType',
                'birthEvent',
                'signature.rsaKey',
                'revocation',
                'verificationLogs',
            ])
        );
    }

    public function update(Certificate $certificate): JsonResponse
    {
        return response()->json([
            'message' => 'Sertifikat yang sudah diterbitkan tidak dapat diedit. Cabut sertifikat lama dan terbitkan sertifikat baru jika data perlu diperbaiki.',
        ], 422);
    }

    public function revoke(Request $request, Certificate $certificate): JsonResponse
    {
        $data = $this->validated($request, [
            'reason' => ['required', 'string'],
            'revoked_at' => ['nullable', 'date'],
        ]);

        if (($certificate->status ?? 'active') === 'revoked') {
            return response()->json(['message' => 'Sertifikat ini sudah dicabut.'], 422);
        }

        if (($certificate->status ?? 'active') === 'expired') {
            return response()->json(['message' => 'Sertifikat yang sudah kedaluwarsa tidak dapat dicabut.'], 422);
        }

        return DB::transaction(function () use ($certificate, $data) {
            CertificateRevocation::query()->updateOrCreate(
                ['certificate_id' => $certificate->id],
                [
                    'revoked_at' => $data['revoked_at'] ?? now(),
                    'reason' => $data['reason'],
                ]
            );

            $certificate->status = 'revoked';
            $certificate->save();

            return response()->json([
                'message' => 'Sertifikat berhasil dicabut.',
                'data' => $certificate->load(['revocation']),
            ]);
        });
    }

    public function unrevoke(Certificate $certificate): JsonResponse
    {
        if ($certificate->status !== 'revoked') {
            return response()->json(['message' => 'Sertifikat ini belum dalam status dicabut.'], 422);
        }

        return DB::transaction(function () use ($certificate) {
            CertificateRevocation::query()
                ->where('certificate_id', $certificate->id)
                ->delete();

            $certificate->status = 'active';
            $certificate->save();

            return response()->json([
                'message' => 'Sertifikat berhasil diaktifkan kembali.',
                'data' => $certificate->fresh()->load(['revocation']),
            ]);
        });
    }

    public function sign(
        Certificate $certificate,
        CertificateSigningService $signingService,
        CertificatePdfIntegrityService $pdfIntegrity
    ): JsonResponse {
        if (($certificate->status ?? 'active') !== 'active') {
            return response()->json([
                'message' => 'Hanya sertifikat aktif yang dapat ditandatangani.',
            ], 422);
        }

        try {
            $sig = $signingService->sign($certificate, true);
            $pdfIntegrity->clearOfficialPdfIntegrity($certificate);

            return response()->json([
                'message' => 'Sertifikat berhasil ditandatangani.',
                'data' => $sig->load('rsaKey'),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Sertifikat belum dapat ditandatangani. Pastikan RSA Key aktif sudah tersedia dan konfigurasi kunci benar.',
            ], 422);
        }
    }

    public function printAuthenticity(Certificate $certificate): View
    {
        $certificate->load([
            'animal.breed',
            'certificateType',
            'signature.rsaKey',
        ]);

        $viewData = app(CertificateViewDataService::class);
        $pdfIntegrity = app(CertificatePdfIntegrityService::class);
        $data = $viewData->build($certificate);
        $qr = $viewData->makeQrBase64($data['verification_url']);
        $assets = $pdfIntegrity->templateAssets();

        return view('certificates.pdf_sertifikat_hewan', compact('certificate', 'data', 'qr', 'assets'));
    }

    public function printBirth(Certificate $certificate): View
    {
        $certificate->load([
            'animal.breed',
            'certificateType',
            'birthEvent.dam.breed',
            'birthEvent.sire.breed',
            'birthEvent.offspringBirths',
            'birthEvent.postnatalCareRecords',
            'signature.rsaKey',
        ]);

        $viewData = app(CertificateViewDataService::class);
        $pdfIntegrity = app(CertificatePdfIntegrityService::class);
        $data = $viewData->build($certificate);
        $qr = $viewData->makeQrBase64($data['verification_url']);
        $assets = $pdfIntegrity->templateAssets();

        return view('certificates.pdf_akte_kelahiran', compact('certificate', 'data', 'qr', 'assets'));
    }

    public function printDeath(Certificate $certificate): View
    {
        $certificate->load([
            'animal.breed',
            'certificateType',
            'signature.rsaKey',
        ]);

        $viewData = app(CertificateViewDataService::class);
        $pdfIntegrity = app(CertificatePdfIntegrityService::class);
        $data = $viewData->build($certificate);
        $qr = $viewData->makeQrBase64($data['verification_url']);
        $assets = $pdfIntegrity->templateAssets();

        return view('certificates.pdf_akte_kematian', compact('certificate', 'data', 'qr', 'assets'));
    }

    private function generateCertificateNumber(string $typeCode, string $issueDate): string
    {
        $documentCode = match ($typeCode) {
            'BIBIT_UNGGUL' => 'SBU',
            'KELAHIRAN' => 'AKL',
            'KEMATIAN' => 'AKM',
            default => 'CERT',
        };

        $year = Carbon::parse($issueDate)->format('Y');
        $prefix = "BBH-{$documentCode}-{$year}";

        $lastCertificate = Certificate::query()
            ->whereHas('certificateType', function ($q) use ($typeCode) {
                $q->where('type_code', $typeCode);
            })
            ->where('certificate_number', 'like', $prefix.'-%')
            ->orderByDesc('id')
            ->first();

        $lastNumber = 0;

        if ($lastCertificate && preg_match('/(\d+)$/', $lastCertificate->certificate_number, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        return $prefix.'-'.str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
    }

    private function buildPayloadSnapshot(array $data): string
    {
        $payload = [
            'barcode_format' => $data['barcode_format'] ?? null,
            'barcode_value' => $data['barcode_value'] ?? null,
            'birth_event_id' => $data['birth_event_id'] ?? null,
            'cause_of_death' => $data['cause_of_death'] ?? null,
            'certificate_number' => $data['certificate_number'],
            'certificate_type_id' => $data['certificate_type_id'],
            'death_date' => $this->snapshotDate($data['death_date'] ?? null),
            'death_time' => $this->snapshotTime($data['death_time'] ?? null),
            'issue_date' => $this->snapshotDate($data['issue_date']),
            'issue_place' => $data['issue_place'] ?? null,
            'valid_from' => $this->snapshotDate($data['valid_from'] ?? null),
            'valid_until' => $this->snapshotDate($data['valid_until'] ?? null),
            'verification_token' => $data['verification_token'],
            'animal_id' => $data['animal_id'],
        ];

        ksort($payload);

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new \RuntimeException('Failed to encode certificate payload snapshot.');
        }

        return $json;
    }

    private function snapshotDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        return Carbon::parse($value)->toDateString();
    }

    private function snapshotTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->format('H:i:s');
        }

        return Carbon::parse($value)->format('H:i:s');
    }
}
