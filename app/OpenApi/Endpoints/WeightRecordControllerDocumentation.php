<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class WeightRecordControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/weight-records',
        tags: ['Weight Records'],
        summary: 'Menampilkan daftar catatan bobot',
        security: [['sanctum' => []]],
        parameters: [
            new OA\QueryParameter(
                name: 'per_page',
                description: 'Jumlah data per halaman (1-100)',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15, minimum: 1, maximum: 100)
            ),            new OA\QueryParameter(
                name: 'animal_id',
                description: 'Filter berdasarkan ID kambing',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
            new OA\QueryParameter(
                name: 'record_date',
                description: 'Filter berdasarkan tanggal pencatatan bobot',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-04-10')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar catatan bobot berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/v1/weight-records',
        tags: ['Weight Records'],
        summary: 'Menambahkan catatan bobot baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['animal_id', 'record_date', 'weight_kg'],
                properties: [
                    new OA\Property(property: 'animal_id', type: 'integer', example: 5),
                    new OA\Property(property: 'record_date', type: 'string', format: 'date', example: '2026-04-10'),
                    new OA\Property(property: 'weight_kg', type: 'number', format: 'float', minimum: 0, example: 2.75),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Bobot stabil'),
                    new OA\Property(property: 'status', type: 'string', nullable: true, enum: ['active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Catatan bobot berhasil dibuat',
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
        path: '/api/v1/weight-records/{weightRecord}',
        tags: ['Weight Records'],
        summary: 'Menampilkan detail catatan bobot',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'weightRecord',
                description: 'ID weight record',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail catatan bobot berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/WeightRecord')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Weight record not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/weight-records/{weightRecord}',
        tags: ['Weight Records'],
        summary: 'Memperbarui catatan bobot',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'weightRecord',
                description: 'ID weight record',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'record_date', type: 'string', format: 'date', example: '2026-04-10'),
                    new OA\Property(property: 'weight_kg', type: 'number', format: 'float', minimum: 0, example: 2.90),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Bobot meningkat'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'inactive'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Catatan bobot berhasil diperbarui',
                content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Weight record not found'),
        ]
    )]
    public function update(): void {}
}
