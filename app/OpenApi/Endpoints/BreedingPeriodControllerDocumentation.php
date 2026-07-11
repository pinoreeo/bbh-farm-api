<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class BreedingPeriodControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/breeding-periods',
        tags: ['Breeding Periods'],
        summary: 'Menampilkan daftar periode breeding',
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
                description: 'Filter status breeding period',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['active', 'closed'], example: 'active')
            ),
            new OA\QueryParameter(
                name: 'include_closed',
                description: 'Tampilkan juga data closed (0 atau 1)',
                required: false,
                schema: new OA\Schema(type: 'integer', enum: [0, 1], example: 0)
            ),
            new OA\QueryParameter(
                name: 'colony_pen_id',
                description: 'Filter berdasarkan ID kandang',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\QueryParameter(
                name: 'male_animal_id',
                description: 'Filter berdasarkan ID pejantan',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
            new OA\QueryParameter(
                name: 'search',
                description: 'Pencarian berdasarkan period code',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'PERIODE-2026-01')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar breeding period berhasil diambil',
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
                                    new OA\Property(property: 'colony_pen_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'period_code', type: 'string', example: 'PERIODE-2026-01'),
                                    new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2026-02-01'),
                                    new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true, example: null),
                                    new OA\Property(property: 'male_animal_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'status', type: 'string', example: 'active'),
                                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Periode kawin pertama'),
                                    new OA\Property(
                                        property: 'colonyPen',
                                        type: 'object',
                                        nullable: true,
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'pen_code', type: 'string', example: 'KAWIN-01'),
                                            new OA\Property(property: 'colony_type', type: 'string', example: 'koloni_kawin'),
                                            new OA\Property(property: 'location', type: 'string', nullable: true, example: 'Kandang A'),
                                        ]
                                    ),
                                    new OA\Property(
                                        property: 'maleAnimal',
                                        type: 'object',
                                        nullable: true,
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'tag_number', type: 'string', example: 'BBH-M-0001'),
                                            new OA\Property(property: 'sex', type: 'string', example: 'male'),
                                            new OA\Property(property: 'generation', type: 'string', example: 'F0'),
                                            new OA\Property(property: 'umur', type: 'string', nullable: true, example: '3 Tahun 2 Bulan'),
                                            new OA\Property(property: 'kategori_umur', type: 'string', example: 'pejantan dewasa'),
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

    #[OA\Post(
        path: '/api/v1/breeding-periods',
        tags: ['Breeding Periods'],
        summary: 'Menambahkan periode breeding baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['colony_pen_id', 'period_code', 'start_date', 'male_animal_id'],
                properties: [
                    new OA\Property(property: 'colony_pen_id', type: 'integer', example: 1),
                    new OA\Property(property: 'period_code', type: 'string', maxLength: 100, example: 'PERIODE-2026-01'),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2026-02-01'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true, example: null),
                    new OA\Property(property: 'male_animal_id', type: 'integer', example: 1),
                    new OA\Property(property: 'status', type: 'string', nullable: true, enum: ['active', 'closed'], example: 'active'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Periode kawin pertama'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Breeding period berhasil dibuat',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Breeding period created.'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error atau validasi bisnis gagal',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'male_animal_id must refer to a male animal.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            additionalProperties: new OA\AdditionalProperties(
                                type: 'array',
                                items: new OA\Items(type: 'string')
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/api/v1/breeding-periods/{breedingPeriod}',
        tags: ['Breeding Periods'],
        summary: 'Menampilkan detail periode breeding',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'breedingPeriod',
                description: 'ID breeding period',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail breeding period berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/BreedingPeriod')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Breeding period not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/breeding-periods/{breedingPeriod}',
        tags: ['Breeding Periods'],
        summary: 'Memperbarui periode breeding',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'breedingPeriod',
                description: 'ID breeding period',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'colony_pen_id', type: 'integer', example: 1),
                    new OA\Property(property: 'period_code', type: 'string', maxLength: 100, example: 'PERIODE-2026-01'),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2026-02-01'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true, example: null),
                    new OA\Property(property: 'male_animal_id', type: 'integer', example: 1),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'closed'], example: 'closed'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Updated note'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Breeding period berhasil diperbarui',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Breeding period updated.'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error atau validasi bisnis gagal',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'colony_pen_id must refer to a breeding pen.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            additionalProperties: new OA\AdditionalProperties(
                                type: 'array',
                                items: new OA\Items(type: 'string')
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Breeding period not found'),
        ]
    )]
    public function update(): void {}
}
