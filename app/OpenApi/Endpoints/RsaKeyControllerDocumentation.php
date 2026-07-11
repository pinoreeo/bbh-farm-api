<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class RsaKeyControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/rsa-keys',
        tags: ['RSA Keys'],
        summary: 'Menampilkan daftar RSA key',
        security: [['sanctum' => []]],
        parameters: [
            new OA\QueryParameter(
                name: 'per_page',
                description: 'Jumlah data per halaman (1-100)',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15, minimum: 1, maximum: 100)
            ),
            new OA\QueryParameter(
                name: 'is_active',
                description: 'Filter status aktif RSA key (0 atau 1)',
                required: false,
                schema: new OA\Schema(type: 'integer', enum: [0, 1], example: 1)
            ),
            new OA\QueryParameter(
                name: 'include_inactive',
                description: 'Tampilkan juga key nonaktif (0 atau 1)',
                required: false,
                schema: new OA\Schema(type: 'integer', enum: [0, 1], example: 0)
            ),
            new OA\QueryParameter(
                name: 'search',
                description: 'Pencarian berdasarkan key_identifier',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'rsa-key-001')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Daftar RSA key berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/v1/rsa-keys',
        tags: ['RSA Keys'],
        summary: 'Menambahkan RSA public key baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['key_identifier', 'public_key_pem', 'key_length'],
                properties: [
                    new OA\Property(property: 'key_identifier', type: 'string', maxLength: 150, example: 'BBH-RSA-2026-001'),
                    new OA\Property(property: 'public_key_pem', type: 'string', example: "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A...\n-----END PUBLIC KEY-----"),
                    new OA\Property(property: 'key_length', type: 'integer', enum: [2048], example: 2048),
                    new OA\Property(property: 'is_active', type: 'boolean', nullable: true, example: true),
                ],
                example: [
                    'key_identifier' => 'BBH-RSA-2026-001',
                    'public_key_pem' => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A...\n-----END PUBLIC KEY-----",
                    'key_length' => 2048,
                    'is_active' => true,
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'RSA key berhasil dibuat', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/api/v1/rsa-keys/{rsaKey}',
        tags: ['RSA Keys'],
        summary: 'Menampilkan detail RSA key',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'rsaKey',
                description: 'ID RSA key',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail RSA key berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/RsaKey')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'RSA key not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Post(
        path: '/api/v1/rsa-keys/generate',
        tags: ['RSA Keys'],
        summary: 'Generate pasangan kunci RSA baru dan aktifkan sebagai key utama',
        description: 'Endpoint ini membuat private key di storage server, menyimpan public key di database, dan menonaktifkan RSA key aktif sebelumnya. Private key tidak dikembalikan lewat response.',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'key_identifier', type: 'string', nullable: true, maxLength: 150, example: 'BBH-RSA-2026-001'),
                    new OA\Property(property: 'key_length', type: 'integer', nullable: true, enum: [2048], example: 2048),
                ],
                example: [
                    'key_identifier' => 'BBH-RSA-2026-001',
                    'key_length' => 2048,
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'RSA key berhasil digenerate', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'Generate RSA key gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function generate(): void {}

    #[OA\Put(
        path: '/api/v1/rsa-keys/{rsaKey}',
        tags: ['RSA Keys'],
        summary: 'Memperbarui RSA key',
        description: 'Fingerprint dihitung otomatis dari public key PEM. Aktivasi key akan menonaktifkan key aktif lain.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'rsaKey',
                description: 'ID RSA key',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'key_identifier', type: 'string', maxLength: 150, example: 'BBH-RSA-2026-002'),
                    new OA\Property(property: 'public_key_pem', type: 'string', example: "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A...\n-----END PUBLIC KEY-----"),
                    new OA\Property(property: 'key_length', type: 'integer', enum: [2048], example: 2048),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
                ],
                example: [
                    'key_identifier' => 'BBH-RSA-2026-002',
                    'is_active' => true,
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'RSA key berhasil diperbarui', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'RSA key not found'),
        ]
    )]
    public function update(): void {}

    #[OA\Post(
        path: '/api/v1/rsa-keys/{rsaKey}/activate',
        tags: ['RSA Keys'],
        summary: 'Mengaktifkan RSA key',
        description: 'Aktivasi satu key akan menonaktifkan key aktif lainnya.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'rsaKey',
                description: 'ID RSA key',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'RSA key berhasil diaktifkan', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'RSA key not found'),
        ]
    )]
    public function activate(): void {}

    #[OA\Post(
        path: '/api/v1/rsa-keys/{rsaKey}/deactivate',
        tags: ['RSA Keys'],
        summary: 'Menonaktifkan RSA key',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'rsaKey',
                description: 'ID RSA key',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'RSA key berhasil dinonaktifkan', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'RSA key already inactive atau tidak boleh menonaktifkan key aktif terakhir', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'RSA key not found'),
        ]
    )]
    public function deactivate(): void {}
}
