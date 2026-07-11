<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class BreedControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/breeds',
        tags: ['Breeds'],
        summary: 'Menampilkan daftar breed',
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
                description: 'Filter status breed',
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
                name: 'search',
                description: 'Pencarian berdasarkan nama breed',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'Saanen')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar breed berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/v1/breeds',
        tags: ['Breeds'],
        summary: 'Menambahkan breed baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['breed_name'],
                properties: [
                    new OA\Property(property: 'breed_name', type: 'string', maxLength: 150, example: 'Saanen'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Ras kambing perah untuk program pembibitan'),
                    new OA\Property(property: 'status', type: 'string', nullable: true, enum: ['active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Breed berhasil dibuat',
                content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/api/v1/breeds/{breed}',
        tags: ['Breeds'],
        summary: 'Menampilkan detail breed',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'breed',
                description: 'ID breed',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail breed berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/Breed')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Breed not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/breeds/{breed}',
        tags: ['Breeds'],
        summary: 'Memperbarui data breed',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'breed',
                description: 'ID breed',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'breed_name', type: 'string', maxLength: 150, example: 'Saanen'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Updated breed description'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Breed berhasil diperbarui',
                content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Breed not found'),
        ]
    )]
    public function update(): void {}
}
