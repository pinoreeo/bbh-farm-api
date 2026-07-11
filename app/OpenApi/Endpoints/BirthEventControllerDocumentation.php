<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class BirthEventControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/birth-events',
        tags: ['Birth Events'],
        summary: 'Menampilkan daftar kejadian kelahiran',
        security: [['sanctum' => []]],
        parameters: [
            new OA\QueryParameter(
                name: 'per_page',
                description: 'Jumlah data per halaman (1-100)',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15, minimum: 1, maximum: 100)
            ),            new OA\QueryParameter(
                name: 'dam_id',
                description: 'Filter berdasarkan ID induk betina',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\QueryParameter(
                name: 'birth_date',
                description: 'Filter berdasarkan tanggal kelahiran',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-04-10')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar birth event berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/v1/birth-events',
        tags: ['Birth Events'],
        summary: 'Menambahkan kejadian kelahiran baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['dam_id', 'birth_date', 'offspring_count', 'birth_process'],
                properties: [
                    new OA\Property(property: 'dam_id', type: 'integer', example: 10),
                    new OA\Property(property: 'sire_id', type: 'integer', nullable: true, example: 12),
                    new OA\Property(property: 'birth_date', type: 'string', format: 'date', example: '2026-04-10'),
                    new OA\Property(property: 'birth_time', type: 'string', nullable: true, example: '08:30:00'),
                    new OA\Property(property: 'offspring_count', type: 'integer', minimum: 1, example: 2),
                    new OA\Property(property: 'birth_process', type: 'string', maxLength: 100, example: 'normal'),
                    new OA\Property(property: 'dam_grade', type: 'string', nullable: true, maxLength: 50, example: 'A'),
                    new OA\Property(property: 'birth_place', type: 'string', nullable: true, maxLength: 255, example: 'Kandang A'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Kelahiran normal'),
                    new OA\Property(property: 'status', type: 'string', nullable: true, enum: ['active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Birth event berhasil dibuat', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/api/v1/birth-events/{birthEvent}',
        tags: ['Birth Events'],
        summary: 'Menampilkan detail kejadian kelahiran',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'birthEvent',
                description: 'ID birth event',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail birth event berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/BirthEvent')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Birth event not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/birth-events/{birthEvent}',
        tags: ['Birth Events'],
        summary: 'Memperbarui kejadian kelahiran',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'birthEvent',
                description: 'ID birth event',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'dam_id', type: 'integer', example: 10),
                    new OA\Property(property: 'sire_id', type: 'integer', nullable: true, example: 12),
                    new OA\Property(property: 'birth_date', type: 'string', format: 'date', example: '2026-04-10'),
                    new OA\Property(property: 'birth_time', type: 'string', nullable: true, example: '08:30:00'),
                    new OA\Property(property: 'offspring_count', type: 'integer', minimum: 1, example: 2),
                    new OA\Property(property: 'birth_process', type: 'string', maxLength: 100, example: 'normal'),
                    new OA\Property(property: 'dam_grade', type: 'string', nullable: true, maxLength: 50, example: 'A'),
                    new OA\Property(property: 'birth_place', type: 'string', nullable: true, maxLength: 255, example: 'Kandang A'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Kelahiran normal diperbarui'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
                ],
                example: [
                    'birth_date' => '2026-04-10',
                    'birth_time' => '08:30:00',
                    'offspring_count' => 2,
                    'notes' => 'Kelahiran normal diperbarui',
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Birth event berhasil diperbarui', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Birth event not found'),
        ]
    )]
    public function update(): void {}
}
