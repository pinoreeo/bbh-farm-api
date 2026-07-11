<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class CertificateExportControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/certificates/{certificate}/qr',
        tags: ['Certificate Exports'],
        summary: 'Mengambil QR code sertifikat dalam format base64',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'certificate', description: 'ID sertifikat', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'QR code berhasil dibuat', content: new OA\JsonContent(ref: '#/components/schemas/CertificateQrResponse')),
            new OA\Response(response: 422, description: 'Barcode value tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate not found'),
        ]
    )]
    public function qr(): void {}

    #[OA\Get(
        path: '/api/v1/certificates/{certificate}/pdf',
        tags: ['Certificate Exports'],
        summary: 'Mengunduh PDF sertifikat',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'certificate', description: 'ID sertifikat', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'PDF sertifikat berhasil dibuat',
                content: new OA\MediaType(
                    mediaType: 'application/pdf',
                    schema: new OA\Schema(type: 'string', format: 'binary')
                )
            ),
            new OA\Response(response: 400, description: 'Jenis sertifikat tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 422, description: 'Data sertifikat belum lengkap untuk export', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate not found'),
        ]
    )]
    public function pdf(): void {}
}
