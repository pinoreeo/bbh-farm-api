<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class PregnancyCheckControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/pregnancy-checks',
        tags: ['Pregnancy Checks'],
        summary: 'Menampilkan daftar pemeriksaan kebuntingan',
        security: [['sanctum' => []]],
        parameters: [
            new OA\QueryParameter(
                name: 'per_page',
                description: 'Jumlah data per halaman (1-100)',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15, minimum: 1, maximum: 100)
            ),            new OA\QueryParameter(
                name: 'breeding_period_id',
                description: 'Filter berdasarkan ID breeding period',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\QueryParameter(
                name: 'female_animal_id',
                description: 'Filter berdasarkan ID kambing betina',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 10)
            ),
            new OA\QueryParameter(
                name: 'is_pregnant',
                description: 'Filter hasil pemeriksaan bunting (0 atau 1)',
                required: false,
                schema: new OA\Schema(type: 'integer', enum: [0, 1], example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar pemeriksaan kebuntingan berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/v1/pregnancy-checks',
        tags: ['Pregnancy Checks'],
        summary: 'Menambahkan pemeriksaan kebuntingan baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['breeding_period_id', 'female_animal_id', 'check_date', 'is_pregnant'],
                properties: [
                    new OA\Property(property: 'breeding_period_id', type: 'integer', example: 1),
                    new OA\Property(property: 'female_animal_id', type: 'integer', example: 10),
                    new OA\Property(property: 'check_date', type: 'string', format: 'date', example: '2026-04-10'),
                    new OA\Property(property: 'is_pregnant', type: 'boolean', example: true),
                    new OA\Property(property: 'method', type: 'string', nullable: true, maxLength: 100, example: 'Palpasi'),
                    new OA\Property(property: 'estimated_gestation_days', type: 'integer', nullable: true, minimum: 0, example: 14),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Indikasi bunting positif'),
                    new OA\Property(property: 'status', type: 'string', nullable: true, enum: ['active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Pemeriksaan kebuntingan berhasil dibuat', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'Validation error atau data duplikat', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/api/v1/pregnancy-checks/{pregnancyCheck}',
        tags: ['Pregnancy Checks'],
        summary: 'Menampilkan detail pemeriksaan kebuntingan',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'pregnancyCheck',
                description: 'ID pregnancy check',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail pemeriksaan kebuntingan berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/PregnancyCheck')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Pregnancy check not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/pregnancy-checks/{pregnancyCheck}',
        tags: ['Pregnancy Checks'],
        summary: 'Memperbarui pemeriksaan kebuntingan',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'pregnancyCheck',
                description: 'ID pregnancy check',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'breeding_period_id', type: 'integer', example: 1),
                    new OA\Property(property: 'female_animal_id', type: 'integer', example: 10),
                    new OA\Property(property: 'check_date', type: 'string', format: 'date', example: '2026-04-11'),
                    new OA\Property(property: 'is_pregnant', type: 'boolean', example: false),
                    new OA\Property(property: 'method', type: 'string', nullable: true, maxLength: 100, example: 'USG'),
                    new OA\Property(property: 'estimated_gestation_days', type: 'integer', nullable: true, minimum: 0, example: 16),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Perlu pemeriksaan lanjutan'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'inactive'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Pemeriksaan kebuntingan berhasil diperbarui', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'Validation error atau konflik data duplikat', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Pregnancy check not found'),
        ]
    )]
    public function update(): void {}
}
