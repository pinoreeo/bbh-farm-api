<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class PostnatalCareRecordControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/postnatal-care-records',
        tags: ['Postnatal Care Records'],
        summary: 'Menampilkan daftar catatan perawatan postnatal',
        security: [['sanctum' => []]],
        parameters: [
            new OA\QueryParameter(
                name: 'per_page',
                description: 'Jumlah data per halaman (1-100)',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15, minimum: 1, maximum: 100)
            ),            new OA\QueryParameter(
                name: 'birth_event_id',
                description: 'Filter berdasarkan ID birth event',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\QueryParameter(
                name: 'target_animal_id',
                description: 'Filter berdasarkan ID target animal',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 12)
            ),
            new OA\QueryParameter(
                name: 'care_date',
                description: 'Filter berdasarkan tanggal perawatan',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-04-10')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar catatan perawatan postnatal berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/v1/postnatal-care-records',
        tags: ['Postnatal Care Records'],
        summary: 'Menambahkan catatan perawatan postnatal baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['birth_event_id', 'target_animal_id', 'care_date'],
                properties: [
                    new OA\Property(property: 'birth_event_id', type: 'integer', example: 1),
                    new OA\Property(property: 'target_animal_id', type: 'integer', example: 12),
                    new OA\Property(property: 'care_date', type: 'string', format: 'date', example: '2026-04-10'),
                    new OA\Property(property: 'administration_method', type: 'string', nullable: true, maxLength: 100, example: 'Oral'),
                    new OA\Property(property: 'volume_ml', type: 'number', format: 'float', nullable: true, minimum: 0, example: 250),
                    new OA\Property(property: 'navel_iodine_status', type: 'string', nullable: true, maxLength: 50, example: 'Ok'),
                    new OA\Property(property: 'vitamin_ade_ml', type: 'number', format: 'float', nullable: true, minimum: 0, example: 2.5),
                    new OA\Property(property: 'vitamin_b_complex_ml', type: 'number', format: 'float', nullable: true, minimum: 0, example: 2.5),
                    new OA\Property(property: 'intracin_ml', type: 'number', format: 'float', nullable: true, minimum: 0, example: 5),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Anak aktif dan responsif'),
                    new OA\Property(property: 'status', type: 'string', nullable: true, enum: ['active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Catatan perawatan postnatal berhasil dibuat',
                content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error atau data duplikat',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/api/v1/postnatal-care-records/{postnatalCareRecord}',
        tags: ['Postnatal Care Records'],
        summary: 'Menampilkan detail catatan perawatan postnatal',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'postnatalCareRecord',
                description: 'ID postnatal care record',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail catatan perawatan postnatal berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/PostnatalCareRecord')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Postnatal care record not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/postnatal-care-records/{postnatalCareRecord}',
        tags: ['Postnatal Care Records'],
        summary: 'Memperbarui catatan perawatan postnatal',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'postnatalCareRecord',
                description: 'ID postnatal care record',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'care_date', type: 'string', format: 'date', example: '2026-04-11'),
                    new OA\Property(property: 'administration_method', type: 'string', nullable: true, maxLength: 100, example: 'Injeksi'),
                    new OA\Property(property: 'volume_ml', type: 'number', format: 'float', nullable: true, minimum: 0, example: 1.5),
                    new OA\Property(property: 'navel_iodine_status', type: 'string', nullable: true, maxLength: 50, example: 'Ok'),
                    new OA\Property(property: 'vitamin_ade_ml', type: 'number', format: 'float', nullable: true, minimum: 0, example: 2.5),
                    new OA\Property(property: 'vitamin_b_complex_ml', type: 'number', format: 'float', nullable: true, minimum: 0, example: 2.5),
                    new OA\Property(property: 'intracin_ml', type: 'number', format: 'float', nullable: true, minimum: 0, example: 5),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Perawatan lanjutan'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'inactive'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Catatan perawatan postnatal berhasil diperbarui',
                content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error atau konflik data duplikat',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Postnatal care record not found'),
        ]
    )]
    public function update(): void {}
}
