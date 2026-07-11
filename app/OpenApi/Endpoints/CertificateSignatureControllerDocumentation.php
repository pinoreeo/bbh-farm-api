<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class CertificateSignatureControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/certificate-signatures',
        tags: ['Certificate Signatures'],
        summary: 'Menampilkan daftar tanda tangan sertifikat',
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
                name: 'rsa_key_id',
                description: 'Filter berdasarkan ID RSA key',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar tanda tangan sertifikat berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Get(
        path: '/api/v1/certificate-signatures/{certificateSignature}',
        tags: ['Certificate Signatures'],
        summary: 'Menampilkan detail tanda tangan sertifikat',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'certificateSignature',
                description: 'ID certificate signature',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail tanda tangan sertifikat berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/CertificateSignature')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate signature not found'),
        ]
    )]
    public function show(): void {}
}
