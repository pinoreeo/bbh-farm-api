<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly AdminActivityLogger $activityLogger) {}

    public function login(Request $request): JsonResponse
    {
        $data = $this->validated($request, [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
            'revoke_existing_tokens' => ['nullable', 'boolean'],
        ]);

        $email = (string) $data['email'];
        $password = (string) $data['password'];

        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['Email atau password tidak sesuai.']]);
        }

        if (($user->role ?? null) !== 'admin') {
            throw ValidationException::withMessages(['email' => ['Email atau password tidak sesuai.']]);
        }

        if ((bool) ($data['revoke_existing_tokens'] ?? false)) {
            $user->tokens()->delete();
        }

        $tokenName = (string) ($data['device_name'] ?? 'api-token');
        $token = $user->createToken($tokenName)->plainTextToken;

        $request->setUserResolver(fn () => $user);
        $this->activityLogger->log($request, 200, 'login', 'auth', [
            'device_name' => $tokenName,
            'revoke_existing_tokens' => (bool) ($data['revoke_existing_tokens'] ?? false),
        ]);

        return response()->json([
            'message' => 'Login berhasil.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $this->validated($request, [
            'email' => ['required', 'email'],
            'reset_url_template' => ['required', 'string', 'max:500'],
        ]);

        $email = (string) $data['email'];
        $user = User::query()
            ->where('email', $email)
            ->where('role', 'admin')
            ->first();

        if ($user) {
            $token = Str::random(64);
            $resetUrl = str_replace('{token}', $token, (string) $data['reset_url_template']);
            $separator = str_contains($resetUrl, '?') ? '&' : '?';
            $resetUrl .= $separator.'email='.urlencode($email);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            Mail::raw(
                "Gunakan tautan berikut untuk mengatur ulang kata sandi admin BBH Farm:\n\n{$resetUrl}\n\nTautan berlaku selama 60 menit.",
                fn ($message) => $message
                    ->to($email)
                    ->subject('Reset Kata Sandi Admin BBH Farm')
            );
        }

        return response()->json([
            'message' => 'Jika email terdaftar, tautan reset kata sandi akan dikirim.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $this->validated($request, [
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = (string) $data['email'];
        $row = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (
            ! $row ||
            ! Hash::check((string) $data['token'], (string) $row->token) ||
            Carbon::parse($row->created_at)->lt(now()->subMinutes(60))
        ) {
            throw ValidationException::withMessages([
                'email' => ['Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.'],
            ]);
        }

        $user = User::query()
            ->where('email', $email)
            ->where('role', 'admin')
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make((string) $data['password']),
        ])->save();

        $user->tokens()->delete();
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return response()->json([
            'message' => 'Kata sandi berhasil diperbarui. Silakan masuk kembali.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json(['message' => 'Sesi login tidak valid. Silakan masuk kembali.'], 401);
        }

        return response()->json($this->userPayload($user));
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user instanceof User) {
            $this->activityLogger->log($request, 200, 'logout', 'auth');

            $user->currentAccessToken()?->delete();
        }

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}
