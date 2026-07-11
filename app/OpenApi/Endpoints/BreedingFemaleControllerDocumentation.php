<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class BreedingFemaleControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/breeding-females',
        tags: ['Breeding Females'],
        summary: 'Menampilkan daftar breeding female',
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
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar breeding female berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/v1/breeding-females',
        tags: ['Breeding Females'],
        summary: 'Menambahkan breeding female baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['breeding_period_id', 'female_animal_id', 'entry_date'],
                properties: [
                    new OA\Property(property: 'breeding_period_id', type: 'integer', example: 1),
                    new OA\Property(property: 'female_animal_id', type: 'integer', example: 10),
                    new OA\Property(property: 'entry_date', type: 'string', format: 'date', example: '2026-04-10'),
                    new OA\Property(property: 'exit_date', type: 'string', format: 'date', nullable: true, example: '2026-05-10'),
                    new OA\Property(property: 'exit_reason', type: 'string', maxLength: 255, nullable: true, example: 'Completed period'),
                    new OA\Property(property: 'status', type: 'string', nullable: true, enum: ['active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Breeding female berhasil dibuat',
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
        path: '/api/v1/breeding-females/{breedingFemale}',
        tags: ['Breeding Females'],
        summary: 'Menampilkan detail breeding female',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'breedingFemale',
                description: 'ID breeding female',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail breeding female berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/BreedingFemale')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Breeding female not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/breeding-females/{breedingFemale}',
        tags: ['Breeding Females'],
        summary: 'Memperbarui data breeding female',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'breedingFemale',
                description: 'ID breeding female',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'entry_date', type: 'string', format: 'date', example: '2026-04-10'),
                    new OA\Property(property: 'exit_date', type: 'string', format: 'date', nullable: true, example: '2026-05-10'),
                    new OA\Property(property: 'exit_reason', type: 'string', maxLength: 255, nullable: true, example: 'Completed period'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'inactive'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Breeding female berhasil diperbarui',
                content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Breeding female not found'),
        ]
    )]
    public function update(): void {}
}
