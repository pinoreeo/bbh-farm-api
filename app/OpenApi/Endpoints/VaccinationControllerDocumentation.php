<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class VaccinationControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/vaccinations',
        tags: ['Vaccinations'],
        summary: 'Menampilkan daftar vaksinasi',
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
                name: 'category_name',
                description: 'Filter berdasarkan jenis vaksin',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'PMK')
            ),
            new OA\QueryParameter(
                name: 'vaccination_date',
                description: 'Filter berdasarkan tanggal vaksinasi',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-04-10')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar vaksinasi berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/v1/vaccinations',
        tags: ['Vaccinations'],
        summary: 'Menambahkan data vaksinasi baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['animal_id', 'category_name', 'vaccination_date', 'product_name'],
                properties: [
                    new OA\Property(property: 'animal_id', type: 'integer', example: 5),
                    new OA\Property(property: 'category_name', type: 'string', maxLength: 100, example: 'PMK'),
                    new OA\Property(property: 'vaccination_date', type: 'string', format: 'date', example: '2026-04-10'),
                    new OA\Property(property: 'product_name', type: 'string', maxLength: 255, example: 'Myxomatosis Vaccine'),
                    new OA\Property(property: 'dosage', type: 'string', nullable: true, maxLength: 100, example: '1 ml'),
                    new OA\Property(property: 'administration_route', type: 'string', nullable: true, maxLength: 100, example: 'Subkutan'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Vaksin dosis pertama'),
                    new OA\Property(property: 'status', type: 'string', nullable: true, enum: ['active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Vaksinasi berhasil dibuat',
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
        path: '/api/v1/vaccinations/{vaccination}',
        tags: ['Vaccinations'],
        summary: 'Menampilkan detail vaksinasi',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'vaccination',
                description: 'ID vaccination',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail vaksinasi berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/Vaccination')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Vaccination not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/vaccinations/{vaccination}',
        tags: ['Vaccinations'],
        summary: 'Memperbarui data vaksinasi',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'vaccination',
                description: 'ID vaccination',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'category_name', type: 'string', maxLength: 100, example: 'PMK'),
                    new OA\Property(property: 'vaccination_date', type: 'string', format: 'date', example: '2026-04-11'),
                    new OA\Property(property: 'product_name', type: 'string', maxLength: 255, example: 'Vaksin Enterotoxemia'),
                    new OA\Property(property: 'dosage', type: 'string', nullable: true, maxLength: 100, example: '0.5 ml'),
                    new OA\Property(property: 'administration_route', type: 'string', nullable: true, maxLength: 100, example: 'Intramuskular'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Booster kedua'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'inactive'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Vaksinasi berhasil diperbarui',
                content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Vaccination not found'),
        ]
    )]
    public function update(): void {}
}
