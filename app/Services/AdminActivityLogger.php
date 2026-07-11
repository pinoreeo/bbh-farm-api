<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AdminActivityLogger
{
    private const MODULE_LABELS = [
        'farm' => 'profil farm',
        'breeds' => 'ras kambing',
        'animals' => 'data kambing',
        'colony-pens' => 'kandang',
        'breeding-periods' => 'periode kawin',
        'breeding-females' => 'betina kawin',
        'pregnancy-checks' => 'kebuntingan',
        'birth-events' => 'kelahiran',
        'offspring-births' => 'cempe lahir',
        'postnatal-care-records' => 'pascalahir',
        'weight-records' => 'catatan bobot',
        'health-treatments' => 'kesehatan',
        'vaccinations' => 'vaksinasi',
        'certificate-types' => 'jenis sertifikat',
        'certificates' => 'akte dan sertifikat',
        'rsa-keys' => 'RSA Key',
        'auth' => 'akun admin',
    ];

    private const ACTION_LABELS = [
        'login' => 'Login admin',
        'logout' => 'Logout admin',
        'create' => 'Menambahkan data',
        'update' => 'Memperbarui data',
        'delete' => 'Menghapus atau menonaktifkan data',
        'sign' => 'Menandatangani sertifikat',
        'revoke' => 'Mencabut sertifikat',
        'unrevoke' => 'Membatalkan pencabutan sertifikat',
        'generate' => 'Membuat kunci RSA',
        'activate' => 'Mengaktifkan data',
        'deactivate' => 'Menonaktifkan data',
        'unknown' => 'Melakukan aktivitas admin',
    ];

    public function log(
        Request $request,
        ?int $statusCode = null,
        ?string $action = null,
        ?string $module = null,
        array $metadata = [],
        ?Response $response = null
    ): void
    {
        $user = $request->user();
        $action ??= $this->resolveAction($request);
        $module ??= $this->resolveModule($request);
        $subject = $this->resolveSubject($request, $response, $module);
        $logMetadata = $metadata ?: $this->metadata($request);

        if ($subject['label'] !== null) {
            $logMetadata['subject_label'] = $subject['label'];
        }

        try {
            AdminActivityLog::query()->create([
                'admin_id' => $user?->id,
                'admin_name' => $user?->name,
                'admin_email' => $user?->email,
                'action' => $action,
                'module' => $module,
                'description' => $this->description($user?->name, $action, $module, $subject['label'], $statusCode),
                'subject_type' => $subject['type'],
                'subject_id' => $subject['id'],
                'method' => $request->method(),
                'path' => $request->path(),
                'status_code' => $statusCode,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => $logMetadata,
            ]);
        } catch (Throwable) {
            report(new \RuntimeException('Admin activity log could not be written.'));
        }
    }

    public function shouldLog(Request $request, int $statusCode): bool
    {
        if ($request->isMethod('GET') || $request->isMethod('HEAD') || $request->isMethod('OPTIONS')) {
            return false;
        }

        if (str_contains($request->path(), 'admin-activity-logs')) {
            return false;
        }

        return $statusCode < 500;
    }

    private function resolveAction(Request $request): string
    {
        $segments = $request->segments();
        $last = end($segments) ?: '';

        if (in_array($last, ['sign', 'revoke', 'unrevoke', 'generate', 'activate', 'deactivate'], true)) {
            return $last;
        }

        return match ($request->method()) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'unknown',
        };
    }

    private function resolveModule(Request $request): string
    {
        $segments = $request->segments();
        $module = $segments[2] ?? 'unknown';

        return (string) $module;
    }

    private function resolveSubject(Request $request, ?Response $response, string $module): array
    {
        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if ($parameter instanceof Model) {
                return [
                    'type' => $parameter::class,
                    'id' => $parameter->getKey(),
                    'label' => $this->subjectLabel($module, $parameter->toArray()),
                ];
            }
        }

        $responseData = $this->responseData($response);
        if (is_array($responseData)) {
            return [
                'type' => null,
                'id' => isset($responseData['id']) ? (int) $responseData['id'] : null,
                'label' => $this->subjectLabel($module, $responseData),
            ];
        }

        return [
            'type' => null,
            'id' => null,
            'label' => $this->subjectLabel($module, $request->all()),
        ];
    }

    private function description(?string $adminName, string $action, string $module, ?string $subjectLabel, ?int $statusCode): string
    {
        $moduleLabel = self::MODULE_LABELS[$module] ?? str($module)->replace('-', ' ')->title()->toString();
        $adminLabel = 'Admin '.($adminName ?: 'sistem');
        $target = $this->targetPhrase($module, $subjectLabel);
        $failed = $statusCode !== null && $statusCode >= 400;

        if ($action === 'login') {
            return "{$adminLabel} telah login.";
        }

        if ($action === 'logout') {
            return "{$adminLabel} telah logout.";
        }

        $verb = match ($action) {
            'create' => 'menambahkan',
            'update' => 'memperbarui',
            'delete', 'deactivate' => 'menonaktifkan',
            'sign' => 'menandatangani',
            'revoke' => 'mencabut',
            'unrevoke' => 'mengaktifkan kembali',
            'generate' => 'membuat',
            'activate' => 'mengaktifkan',
            default => 'memproses',
        };

        if ($failed) {
            return "{$adminLabel} mencoba {$verb} {$moduleLabel}{$target}, tetapi belum berhasil.";
        }

        return "{$adminLabel} {$verb} {$moduleLabel}{$target}.";
    }

    private function targetPhrase(string $module, ?string $subjectLabel): string
    {
        if ($subjectLabel === null || $subjectLabel === '') {
            return '';
        }

        return match ($module) {
            'animals' => " dengan tag {$subjectLabel}",
            'colony-pens' => " dengan kode kandang {$subjectLabel}",
            'breeding-periods' => " dengan kode periode {$subjectLabel}",
            'certificates' => " dengan nomor sertifikat {$subjectLabel}",
            'rsa-keys' => " dengan key identifier {$subjectLabel}",
            'breeds' => " {$subjectLabel}",
            default => " dengan ID {$subjectLabel}",
        };
    }

    private function subjectLabel(string $module, array $data): ?string
    {
        $label = match ($module) {
            'animals' => $data['tag_number'] ?? null,
            'colony-pens' => $data['pen_code'] ?? null,
            'breeding-periods' => $data['period_code'] ?? null,
            'breeding-females' => $this->breedingFemaleLabel($data),
            'pregnancy-checks' => $this->pregnancyCheckLabel($data),
            'birth-events' => $this->birthEventLabel($data),
            'offspring-births' => $this->relatedAnimalLabel($data, 'offspring_animal'),
            'postnatal-care-records' => $this->relatedAnimalLabel($data, 'target_animal'),
            'weight-records', 'health-treatments', 'vaccinations' => $this->relatedAnimalLabel($data, 'animal'),
            'certificates' => $data['certificate_number'] ?? null,
            'rsa-keys' => $data['key_identifier'] ?? null,
            'breeds' => $data['breed_name'] ?? null,
            'certificate-types' => $data['type_name'] ?? null,
            default => null,
        };

        if ($label !== null && $label !== '') {
            return (string) $label;
        }

        return isset($data['id']) ? '#'.$data['id'] : null;
    }

    private function breedingFemaleLabel(array $data): ?string
    {
        $tag = $data['female_animal']['tag_number'] ?? null;
        $period = $data['breeding_period']['period_code'] ?? null;

        if ($tag && $period) {
            return "{$tag} pada periode {$period}";
        }

        return $tag ?? $period ?? null;
    }

    private function pregnancyCheckLabel(array $data): ?string
    {
        $tag = $data['female_animal']['tag_number']
            ?? $data['breeding_female']['female_animal']['tag_number']
            ?? null;
        $period = $data['breeding_period']['period_code']
            ?? $data['breeding_female']['breeding_period']['period_code']
            ?? null;
        $date = isset($data['check_date']) ? substr((string) $data['check_date'], 0, 10) : null;

        $parts = array_filter([$tag, $period ? "periode {$period}" : null, $date ? "tanggal {$date}" : null]);

        return $parts !== [] ? implode(' ', $parts) : null;
    }

    private function birthEventLabel(array $data): ?string
    {
        $dam = $data['dam']['tag_number'] ?? null;
        $date = isset($data['birth_date']) ? substr((string) $data['birth_date'], 0, 10) : null;

        if ($dam && $date) {
            return "induk {$dam} tanggal {$date}";
        }

        return $dam ?? $date ?? null;
    }

    private function relatedAnimalLabel(array $data, string $relation): ?string
    {
        return $data[$relation]['tag_number']
            ?? $data['animal']['tag_number']
            ?? $data['target_animal']['tag_number']
            ?? $data['offspring_animal']['tag_number']
            ?? null;
    }

    private function responseData(?Response $response): ?array
    {
        if ($response === null || $response->getStatusCode() >= 400) {
            return null;
        }

        $content = $response->getContent();
        if (! is_string($content) || $content === '') {
            return null;
        }

        $json = json_decode($content, true);
        if (! is_array($json)) {
            return null;
        }

        return is_array($json['data'] ?? null) ? $json['data'] : $json;
    }

    private function metadata(Request $request): array
    {
        $payload = collect($request->except(['password', 'password_confirmation', 'token']))
            ->map(fn ($value) => is_string($value) && strlen($value) > 500 ? substr($value, 0, 500).'...' : $value)
            ->all();

        return [
            'route' => $request->route()?->getName(),
            'payload' => $payload,
        ];
    }
}
