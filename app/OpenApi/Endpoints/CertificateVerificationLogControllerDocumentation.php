<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class CertificateVerificationLogControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/certificate-verification-logs',
        tags: ['Certificate Verification Logs'],
        summary: 'Menampilkan daftar log verifikasi sertifikat',
        security: [['sanctum' => []]],
        parameters: [
            new OA\QueryParameter(
                name: 'per_page',
                description: 'Jumlah data per halaman (1-100)',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15, minimum: 1, maximum: 100)
            ),
            new OA\QueryParameter(
                name: 'certificate_id',
                description: 'Filter berdasarkan ID sertifikat',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 8)
            ),
            new OA\QueryParameter(
                name: 'is_valid',
                description: 'Filter validitas hasil verifikasi (0 atau 1)',
                required: false,
                schema: new OA\Schema(type: 'integer', enum: [0, 1], example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar log verifikasi sertifikat berhasil diambil',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'certificate_id', type: 'integer', example: 8),
                                    new OA\Property(property: 'verification_time', type: 'string', format: 'date-time', example: '2026-04-10T08:30:00.000000Z'),
                                    new OA\Property(property: 'is_valid', type: 'boolean', example: true),
                                    new OA\Property(property: 'certificate_status_at_verification', type: 'string', nullable: true, example: 'active'),
                                    new OA\Property(property: 'failure_reason', type: 'string', nullable: true, example: null),
                                    new OA\Property(property: 'used_key_fingerprint', type: 'string', nullable: true, example: 'abc123fingerprint'),
                                    new OA\Property(property: 'used_barcode_value', type: 'string', nullable: true, example: 'http://127.0.0.1:8000/api/v1/public/certificates/verify/550e8400-e29b-41d4-a716-446655440000'),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true, example: '2026-04-10T08:30:00.000000Z'),
                                    new OA\Property(
                                        property: 'certificate',
                                        type: 'object',
                                        nullable: true,
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 8),
                                            new OA\Property(property: 'certificate_number', type: 'string', example: 'BBH-SBU-2026-0008'),
                                            new OA\Property(property: 'verification_token', type: 'string', nullable: true, example: '550e8400-e29b-41d4-a716-446655440000'),
                                            new OA\Property(property: 'status', type: 'string', example: 'active'),
                                        ]
                                    ),
                                ]
                            )
                        ),
                        new OA\Property(property: 'per_page', type: 'integer', example: 15),
                        new OA\Property(property: 'total', type: 'integer', example: 20),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Get(
        path: '/api/v1/certificate-verification-logs/{certificateVerificationLog}',
        tags: ['Certificate Verification Logs'],
        summary: 'Menampilkan detail log verifikasi sertifikat',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'certificateVerificationLog',
                description: 'ID certificate verification log',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail log verifikasi sertifikat berhasil diambil',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'certificate_id', type: 'integer', example: 8),
                        new OA\Property(property: 'verification_time', type: 'string', format: 'date-time', example: '2026-04-10T08:30:00.000000Z'),
                        new OA\Property(property: 'is_valid', type: 'boolean', example: true),
                        new OA\Property(property: 'certificate_status_at_verification', type: 'string', nullable: true, example: 'active'),
                        new OA\Property(property: 'failure_reason', type: 'string', nullable: true, example: null),
                        new OA\Property(property: 'used_key_fingerprint', type: 'string', nullable: true, example: 'abc123fingerprint'),
                        new OA\Property(property: 'used_barcode_value', type: 'string', nullable: true, example: 'http://127.0.0.1:8000/api/v1/public/certificates/verify/550e8400-e29b-41d4-a716-446655440000'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true, example: '2026-04-10T08:30:00.000000Z'),
                        new OA\Property(
                            property: 'certificate',
                            type: 'object',
                            nullable: true,
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 8),
                                new OA\Property(property: 'certificate_number', type: 'string', example: 'BBH-SBU-2026-0008'),
                                new OA\Property(property: 'verification_token', type: 'string', nullable: true, example: '550e8400-e29b-41d4-a716-446655440000'),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate verification log not found'),
        ]
    )]
    public function show(): void {}
}
