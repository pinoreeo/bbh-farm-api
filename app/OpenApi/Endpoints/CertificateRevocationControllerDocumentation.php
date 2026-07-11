<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class CertificateRevocationControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/certificate-revocations',
        tags: ['Certificate Revocations'],
        summary: 'Menampilkan daftar pencabutan sertifikat',
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
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar pencabutan sertifikat berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Get(
        path: '/api/v1/certificate-revocations/{certificateRevocation}',
        tags: ['Certificate Revocations'],
        summary: 'Menampilkan detail pencabutan sertifikat',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'certificateRevocation',
                description: 'ID certificate revocation',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail pencabutan sertifikat berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/CertificateRevocation')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate revocation not found'),
        ]
    )]
    public function show(): void {}
}
