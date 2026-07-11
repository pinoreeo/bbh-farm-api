<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class ColonyPenControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/colony-pens',
        tags: ['Colony Pens'],
        summary: 'Menampilkan daftar kandang koloni',
        security: [['sanctum' => []]],
        parameters: [
            new OA\QueryParameter(
                name: 'per_page',
                description: 'Jumlah data per halaman (1-100)',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15, minimum: 1, maximum: 100)
            ),            new OA\QueryParameter(
                name: 'colony_type',
                description: 'Filter berdasarkan tipe koloni',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'koloni_kawin')
            ),
            new OA\QueryParameter(
                name: 'search',
                description: 'Pencarian berdasarkan pen_code',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'PEN-001')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar kandang koloni berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/v1/colony-pens',
        tags: ['Colony Pens'],
        summary: 'Menambahkan kandang koloni baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['pen_code', 'colony_type'],
                properties: [
                    new OA\Property(property: 'pen_code', type: 'string', maxLength: 100, example: 'PEN-001'),
                    new OA\Property(property: 'colony_type', type: 'string', maxLength: 100, example: 'koloni_kawin'),
                    new OA\Property(property: 'location', type: 'string', nullable: true, maxLength: 255, example: 'Blok A'),
                    new OA\Property(property: 'capacity', type: 'integer', nullable: true, minimum: 0, example: 20),
                    new OA\Property(property: 'status', type: 'string', nullable: true, enum: ['active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Kandang koloni berhasil dibuat',
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
        path: '/api/v1/colony-pens/{colonyPen}',
        tags: ['Colony Pens'],
        summary: 'Menampilkan detail kandang koloni',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'colonyPen',
                description: 'ID colony pen',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail kandang koloni berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/ColonyPen')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Colony pen not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/colony-pens/{colonyPen}',
        tags: ['Colony Pens'],
        summary: 'Memperbarui kandang koloni',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'colonyPen',
                description: 'ID colony pen',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'pen_code', type: 'string', maxLength: 100, example: 'PEN-001'),
                    new OA\Property(property: 'colony_type', type: 'string', maxLength: 100, example: 'koloni_kawin'),
                    new OA\Property(property: 'location', type: 'string', nullable: true, maxLength: 255, example: 'Blok B'),
                    new OA\Property(property: 'capacity', type: 'integer', nullable: true, minimum: 0, example: 25),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'inactive'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Kandang koloni berhasil diperbarui',
                content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Colony pen not found'),
        ]
    )]
    public function update(): void {}
}
