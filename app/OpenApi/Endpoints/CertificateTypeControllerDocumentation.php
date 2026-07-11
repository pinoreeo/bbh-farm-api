<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class CertificateTypeControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/certificate-types',
        tags: ['Certificate Types'],
        summary: 'Menampilkan daftar jenis sertifikat',
        security: [['sanctum' => []]],
        parameters: [
            new OA\QueryParameter(
                name: 'per_page',
                description: 'Jumlah data per halaman (1-100)',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15, minimum: 1, maximum: 100)
            ),
            new OA\QueryParameter(
                name: 'status',
                description: 'Filter status jenis sertifikat',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['active', 'inactive'], example: 'active')
            ),
            new OA\QueryParameter(
                name: 'include_inactive',
                description: 'Tampilkan juga data inactive (0 atau 1)',
                required: false,
                schema: new OA\Schema(type: 'integer', enum: [0, 1], example: 0)
            ),
            new OA\QueryParameter(
                name: 'type_code',
                description: 'Filter berdasarkan kode jenis sertifikat',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['BIBIT_UNGGUL', 'KELAHIRAN', 'KEMATIAN'], example: 'KELAHIRAN')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar jenis sertifikat berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Get(
        path: '/api/v1/certificate-types/{certificateType}',
        tags: ['Certificate Types'],
        summary: 'Menampilkan detail jenis sertifikat',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'certificateType',
                description: 'ID certificate type',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail jenis sertifikat berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/CertificateType')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate type not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/certificate-types/{certificateType}',
        tags: ['Certificate Types'],
        summary: 'Memperbarui jenis sertifikat',
        description: 'Hanya field non-kunci yang dapat diubah. type_code tidak dapat diubah karena merupakan master tetap sistem.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'certificateType',
                description: 'ID certificate type',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'type_name',
                        type: 'string',
                        nullable: true,
                        maxLength: 255,
                        example: 'Akta Kelahiran'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Versi pembaruan deskripsi sertifikat'
                    ),
                    new OA\Property(
                        property: 'template_version',
                        type: 'string',
                        nullable: true,
                        maxLength: 50,
                        example: 'v2'
                    ),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'inactive'],
                        example: 'inactive'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Jenis sertifikat berhasil diperbarui',
                content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate type not found'),
        ]
    )]
    public function update(): void {}
}
