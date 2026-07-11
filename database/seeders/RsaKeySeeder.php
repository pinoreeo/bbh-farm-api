<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RsaKeySeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = $this->stringConfig('bbh.admin.email', 'admin@example.com');
        $admin = DB::table('sys_users')->where('email', $adminEmail)->first();

        if (! $admin) {
            throw new \RuntimeException('Default admin user must exist before seeding an RSA key.');
        }

        $activeKey = DB::table('cert_rsa_keys')
            ->where('user_id', $admin->id)
            ->where('is_active', 1)
            ->first();

        if ($this->hasUsableActiveSigningKey($activeKey)) {
            return;
        }

        $keyIdentifier = 'RSA-DEMO-'.now()->format('YmdHis');
        $keyLength = 2048;
        $keyResource = openssl_pkey_new([
            'private_key_bits' => $keyLength,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ] + $this->opensslConfigArgs());

        if ($keyResource === false) {
            throw new \RuntimeException('Failed to generate demo RSA key pair.');
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
            throw new \RuntimeException('Failed to export demo RSA private key.');
        }

        $keyDetails = openssl_pkey_get_details($keyResource);
        if (! is_array($keyDetails) || empty($keyDetails['key'])) {
            throw new \RuntimeException('Failed to read demo RSA public key.');
        }

        $directory = storage_path('app/keys/rsa');
        File::ensureDirectoryExists($directory, 0700, true);

        $privateKeyPath = $directory.DIRECTORY_SEPARATOR.'rsa_demo_private.pem';
        if (File::put($privateKeyPath, $privateKeyPem, true) === false) {
            throw new \RuntimeException('Failed to write demo RSA private key.');
        }

        @chmod($privateKeyPath, 0600);

        $publicKeyPem = trim($keyDetails['key']);

        DB::table('cert_rsa_keys')->insert([
            'user_id' => $admin->id,
            'key_identifier' => $keyIdentifier,
            'public_key_pem' => $publicKeyPem,
            'private_key_path' => $privateKeyPath,
            'algorithm' => 'RSA',
            'key_length' => $keyLength,
            'fingerprint_sha256' => hash('sha256', $publicKeyPem),
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function hasUsableActiveSigningKey(?object $activeKey): bool
    {
        if (! $activeKey) {
            return false;
        }

        $publicKeyPem = $activeKey->public_key_pem ?? null;
        $privateKeyPath = $activeKey->private_key_path ?? null;

        if (
            ! is_string($publicKeyPem) || trim($publicKeyPem) === '' ||
            ! is_string($privateKeyPath) || trim($privateKeyPath) === '' ||
            ! is_file($privateKeyPath)
        ) {
            return false;
        }

        $privateKeyPem = file_get_contents($privateKeyPath);
        if ($privateKeyPem === false || trim($privateKeyPem) === '') {
            return false;
        }

        $passphrase = config('bbh_signing.private_key_passphrase');
        $privateKey = openssl_pkey_get_private(
            $privateKeyPem,
            is_string($passphrase) ? $passphrase : ''
        );

        if ($privateKey === false) {
            return false;
        }

        $details = openssl_pkey_get_details($privateKey);

        return is_array($details) &&
            isset($details['key']) &&
            $this->normalizePem((string) $details['key']) === $this->normalizePem($publicKeyPem);
    }

    private function normalizePem(string $pem): string
    {
        return preg_replace('/\s+/', '', trim($pem)) ?? '';
    }

    private function stringConfig(string $key, string $default): string
    {
        $value = config($key);

        return is_string($value) && $value !== '' ? $value : $default;
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
