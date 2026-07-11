<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class OffspringBirthControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/offspring-births',
        tags: ['Offspring Births'],
        summary: 'Menampilkan daftar data kelahiran anak',
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
                name: 'offspring_animal_id',
                description: 'Filter berdasarkan ID kambing anak',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 12)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar offspring birth berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/v1/offspring-births',
        tags: ['Offspring Births'],
        summary: 'Menambahkan data kelahiran anak baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['birth_event_id', 'offspring_animal_id', 'birth_weight_kg'],
                properties: [
                    new OA\Property(property: 'birth_event_id', type: 'integer', example: 1),
                    new OA\Property(property: 'offspring_animal_id', type: 'integer', example: 12),
                    new OA\Property(property: 'birth_weight_kg', type: 'number', format: 'float', minimum: 0, example: 3.20),
                    new OA\Property(property: 'birth_status', type: 'string', nullable: true, enum: ['alive', 'dead'], example: 'alive'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Lahir normal'),
                    new OA\Property(property: 'status', type: 'string', nullable: true, enum: ['active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Offspring birth berhasil dibuat', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'Validation error atau data duplikat', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/api/v1/offspring-births/{offspringBirth}',
        tags: ['Offspring Births'],
        summary: 'Menampilkan detail data kelahiran anak',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'offspringBirth',
                description: 'ID offspring birth',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail offspring birth berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/OffspringBirth')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Offspring birth not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/offspring-births/{offspringBirth}',
        tags: ['Offspring Births'],
        summary: 'Memperbarui data kelahiran anak',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'offspringBirth',
                description: 'ID offspring birth',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'birth_weight_kg', type: 'number', format: 'float', minimum: 0, example: 3.25),
                    new OA\Property(property: 'birth_status', type: 'string', enum: ['alive', 'dead'], example: 'alive'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Berat lahir diperbarui'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'inactive'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Offspring birth berhasil diperbarui', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Offspring birth not found'),
        ]
    )]
    public function update(): void {}
}
