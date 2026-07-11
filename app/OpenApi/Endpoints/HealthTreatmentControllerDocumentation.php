<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class HealthTreatmentControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/health-treatments',
        tags: ['Health Treatments'],
        summary: 'Menampilkan daftar treatment kesehatan',
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
                name: 'treatment_date',
                description: 'Filter berdasarkan tanggal treatment',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-04-10')
            ),
            new OA\QueryParameter(
                name: 'treatment_group',
                description: 'Filter berdasarkan kelompok treatment',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'Antibiotik')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar treatment kesehatan berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/v1/health-treatments',
        tags: ['Health Treatments'],
        summary: 'Menambahkan treatment kesehatan baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['animal_id', 'treatment_group', 'product_name', 'treatment_date'],
                properties: [
                    new OA\Property(property: 'animal_id', type: 'integer', example: 5),
                    new OA\Property(property: 'treatment_group', type: 'string', maxLength: 100, example: 'Antibiotik'),
                    new OA\Property(property: 'product_name', type: 'string', maxLength: 255, example: 'Oxytetracycline'),
                    new OA\Property(property: 'treatment_date', type: 'string', format: 'date', example: '2026-04-10'),
                    new OA\Property(property: 'dosage', type: 'string', nullable: true, maxLength: 100, example: '2 ml'),
                    new OA\Property(property: 'administration_route', type: 'string', nullable: true, maxLength: 100, example: 'Intramuskular'),
                    new OA\Property(property: 'action_category', type: 'string', nullable: true, maxLength: 100, example: 'Kuratif'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Kondisi membaik setelah 3 hari'),
                    new OA\Property(property: 'status', type: 'string', nullable: true, enum: ['active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Treatment kesehatan berhasil dibuat',
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
        path: '/api/v1/health-treatments/{healthTreatment}',
        tags: ['Health Treatments'],
        summary: 'Menampilkan detail treatment kesehatan',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'healthTreatment',
                description: 'ID health treatment',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail treatment kesehatan berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/HealthTreatment')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Health treatment not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/health-treatments/{healthTreatment}',
        tags: ['Health Treatments'],
        summary: 'Memperbarui treatment kesehatan',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'healthTreatment',
                description: 'ID health treatment',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'treatment_group', type: 'string', maxLength: 100, example: 'Vitamin'),
                    new OA\Property(property: 'product_name', type: 'string', maxLength: 255, example: 'Vitamin B Complex'),
                    new OA\Property(property: 'treatment_date', type: 'string', format: 'date', example: '2026-04-11'),
                    new OA\Property(property: 'dosage', type: 'string', nullable: true, maxLength: 100, example: '1 ml'),
                    new OA\Property(property: 'administration_route', type: 'string', nullable: true, maxLength: 100, example: 'Oral'),
                    new OA\Property(property: 'action_category', type: 'string', nullable: true, maxLength: 100, example: 'Preventif'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Perlu observasi lanjutan'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'inactive'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Treatment kesehatan berhasil diperbarui',
                content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Health treatment not found'),
        ]
    )]
    public function update(): void {}
}
