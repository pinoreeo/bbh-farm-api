<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RsaKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RsaKeyController extends Controller
{
    private const KEY_LENGTH = 2048;

    public function index(Request $request): JsonResponse
    {
        $perPage = $this->perPage($request);
        $q = RsaKey::query()
            ->where('user_id', $request->user()?->id);

        if ($request->filled('is_active')) {
            $q->where('is_active', (int) $request->query('is_active') ? 1 : 0);
        } else {
            if (! (int) $request->query('include_inactive', 0)) {
                $q->where('is_active', 1);
            }
        }

        if ($request->filled('search')) {
            $s = trim($request->query('search'));
            $q->where('key_identifier', 'like', "%{$s}%");
        }

        return response()->json(
            $q->orderByDesc('is_active')
                ->orderByDesc('id')
                ->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'User tidak terautentikasi.'], 401);
        }

        if (RsaKey::query()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'User ini sudah memiliki RSA Key.',
            ], 422);
        }

        $data = $this->validated($request, [
            'key_identifier' => ['required', 'string', 'max:150', 'unique:cert_rsa_keys,key_identifier'],
            'public_key_pem' => ['required', 'string'],
            'key_length' => ['required', 'integer', Rule::in([self::KEY_LENGTH])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $normalizedPem = trim($data['public_key_pem']);

        $publicKey = openssl_pkey_get_public($normalizedPem);
        if ($publicKey === false) {
            return response()->json([
                'message' => 'Format public key RSA tidak valid.',
            ], 422);
        }

        $fingerprint = hash('sha256', $normalizedPem);
        $isActive = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        if (RsaKey::query()->where('fingerprint_sha256', $fingerprint)->exists()) {
            return response()->json([
                'message' => 'RSA Key dengan fingerprint tersebut sudah terdaftar.',
            ], 422);
        }

        $row = DB::transaction(function () use ($data, $normalizedPem, $fingerprint, $isActive, $user) {
            return RsaKey::query()->create([
                'user_id' => $user->id,
                'key_identifier' => $data['key_identifier'],
                'public_key_pem' => $normalizedPem,
                'algorithm' => 'RSA',
                'key_length' => $data['key_length'],
                'fingerprint_sha256' => $fingerprint,
                'is_active' => $isActive ? 1 : 0,
            ]);
        });

        return response()->json([
            'message' => 'RSA Key berhasil disimpan.',
            'data' => $row,
        ], 201);
    }

    public function show(RsaKey $rsaKey): JsonResponse
    {
        if ($rsaKey->user_id !== request()->user()?->id) {
            abort(404);
        }

        return response()->json($rsaKey);
    }

    public function generate(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'User tidak terautentikasi.'], 401);
        }

        if (RsaKey::query()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'User ini sudah memiliki RSA Key.',
            ], 422);
        }

        $data = $this->validated($request, [
            'key_identifier' => ['nullable', 'string', 'max:150', 'unique:cert_rsa_keys,key_identifier'],
            'key_length' => ['nullable', 'integer', Rule::in([self::KEY_LENGTH])],
        ]);

        $keyLength = self::KEY_LENGTH;
        $keyIdentifier = $data['key_identifier']
            ?? 'BBH-RSA-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));

        $keyResource = openssl_pkey_new([
            'private_key_bits' => $keyLength,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ] + $this->opensslConfigArgs());

        if ($keyResource === false) {
            return response()->json([
                'message' => 'RSA Key belum dapat dibuat. Pastikan OpenSSL tersedia pada server.',
            ], 422);
        }

        $privateKeyPem = '';
        $passphrase = config('bbh_signing.private_key_passphrase');

        $exported = openssl_pkey_export(
            $keyResource,
            $privateKeyPem,
            is_string($passphrase) && $passphrase !== '' ? $passphrase : null,
            $this->opensslConfigArgs()
        );

        if ($exported !== true || trim($privateKeyPem) === '') {
            return response()->json([
                'message' => 'Private key RSA belum dapat diekspor.',
            ], 422);
        }

        $keyDetails = openssl_pkey_get_details($keyResource);
        if (! is_array($keyDetails) || empty($keyDetails['key'])) {
            return response()->json([
                'message' => 'Public key RSA hasil generate belum dapat dibaca.',
            ], 422);
        }

        $publicKeyPem = trim($keyDetails['key']);
        $fingerprint = hash('sha256', $publicKeyPem);

        if (RsaKey::query()->where('fingerprint_sha256', $fingerprint)->exists()) {
            return response()->json([
                'message' => 'RSA Key dengan fingerprint tersebut sudah terdaftar.',
            ], 422);
        }

        $directory = storage_path('app/keys/rsa');
        File::ensureDirectoryExists($directory, 0700, true);

        $fileName = $this->safeKeyFileName($keyIdentifier);
        $privateKeyPath = $directory.DIRECTORY_SEPARATOR.$fileName.'_private.pem';

        if (is_file($privateKeyPath)) {
            return response()->json([
                'message' => 'File private key untuk key identifier tersebut sudah ada.',
            ], 422);
        }

        if (File::put($privateKeyPath, $privateKeyPem, true) === false) {
            return response()->json([
                'message' => 'File private key RSA belum dapat disimpan.',
            ], 422);
        }

        @chmod($privateKeyPath, 0600);

        $row = DB::transaction(function () use ($keyIdentifier, $publicKeyPem, $privateKeyPath, $keyLength, $fingerprint, $user) {
            return RsaKey::query()->create([
                'user_id' => $user->id,
                'key_identifier' => $keyIdentifier,
                'public_key_pem' => $publicKeyPem,
                'private_key_path' => $privateKeyPath,
                'algorithm' => 'RSA',
                'key_length' => $keyLength,
                'fingerprint_sha256' => $fingerprint,
                'is_active' => 1,
            ]);
        });

        return response()->json([
            'message' => 'RSA Key berhasil dibuat dan diaktifkan.',
            'data' => $row,
        ], 201);
    }

    public function update(Request $request, RsaKey $rsaKey): JsonResponse
    {
        if ($rsaKey->user_id !== $request->user()?->id) {
            abort(404);
        }

        $data = $this->validated($request, [
            'key_identifier' => ['sometimes', 'string', 'max:150', Rule::unique('cert_rsa_keys', 'key_identifier')->ignore($rsaKey->id)],
            'public_key_pem' => ['sometimes', 'string'],
            'key_length' => ['sometimes', 'integer', Rule::in([self::KEY_LENGTH])],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return DB::transaction(function () use ($data, $rsaKey) {
            if (array_key_exists('public_key_pem', $data)) {
                $normalizedPem = trim($data['public_key_pem']);

                $publicKey = openssl_pkey_get_public($normalizedPem);
                if ($publicKey === false) {
                    return response()->json([
                        'message' => 'Format public key RSA tidak valid.',
                    ], 422);
                }

                $fingerprint = hash('sha256', $normalizedPem);

                $duplicateFingerprint = RsaKey::query()
                    ->where('id', '!=', $rsaKey->id)
                    ->where('fingerprint_sha256', $fingerprint)
                    ->exists();

                if ($duplicateFingerprint) {
                    return response()->json([
                        'message' => 'RSA Key dengan fingerprint tersebut sudah terdaftar.',
                    ], 422);
                }

                $data['public_key_pem'] = $normalizedPem;
                $data['fingerprint_sha256'] = $fingerprint;
            }

            $data['algorithm'] = 'RSA';

            if (array_key_exists('is_active', $data)) {
                $newActive = (bool) $data['is_active'];

                if ($newActive) {
                    $data['is_active'] = 1;
                } else {
                    $data['is_active'] = 0;
                }
            }

            $rsaKey->fill($data)->save();

            return response()->json([
                'message' => 'RSA Key berhasil diperbarui.',
                'data' => $rsaKey,
            ]);
        });
    }

    public function activate(RsaKey $rsaKey): JsonResponse
    {
        if ($rsaKey->user_id !== request()->user()?->id) {
            abort(404);
        }

        $rsaKey->is_active = 1;
        $rsaKey->save();

        return response()->json([
            'message' => 'RSA Key berhasil diaktifkan.',
            'data' => $rsaKey->fresh(),
        ]);
    }

    public function deactivate(RsaKey $rsaKey): JsonResponse
    {
        if ($rsaKey->user_id !== request()->user()?->id) {
            abort(404);
        }

        if ((int) $rsaKey->is_active === 0) {
            return response()->json([
                'message' => 'RSA Key ini sudah nonaktif.',
            ], 422);
        }

        $rsaKey->is_active = 0;
        $rsaKey->save();

        return response()->json([
            'message' => 'RSA Key berhasil dinonaktifkan.',
            'data' => $rsaKey,
        ]);
    }

    private function safeKeyFileName(string $keyIdentifier): string
    {
        $slug = Str::slug($keyIdentifier, '_');

        return $slug !== '' ? $slug : 'rsa_key_'.now()->format('YmdHis');
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
