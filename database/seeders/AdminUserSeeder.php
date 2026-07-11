<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the default admin account.
     */
    public function run(): void
    {
        $adminEmail = $this->stringConfig('bbh.admin.email', 'admin@example.com');
        $adminPassword = $this->stringConfig('bbh.admin.password', 'password');

        if ($adminPassword === '' || (! app()->environment('local') && $adminPassword === 'password')) {
            throw new \RuntimeException('BBH_ADMIN_PASSWORD must be configured before seeding the default admin.');
        }

        DB::table('sys_users')->updateOrInsert(
            ['email' => $adminEmail],
            [
                'name' => $this->stringConfig('bbh.admin.name', 'John Doe'),
                'email_verified_at' => now(),
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function stringConfig(string $key, string $default): string
    {
        $value = config($key);

        return is_string($value) && $value !== '' ? $value : $default;
    }
}
