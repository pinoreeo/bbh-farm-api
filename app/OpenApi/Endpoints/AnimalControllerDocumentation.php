<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class AnimalControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/animals',
        tags: ['Animals'],
        summary: 'Menampilkan daftar kambing',
        security: [['sanctum' => []]],
        parameters: [
            new OA\QueryParameter(
                name: 'per_page',
                description: 'Jumlah data per halaman (1-100)',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15, minimum: 1, maximum: 100)
            ),            new OA\QueryParameter(
                name: 'life_status',
                description: 'Filter status hidup kambing',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['alive', 'dead'], example: 'alive')
            ),
            new OA\QueryParameter(
                name: 'sex',
                description: 'Filter jenis kelamin',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['male', 'female'], example: 'female')
            ),
            new OA\QueryParameter(
                name: 'breed_id',
                description: 'Filter berdasarkan ID breed',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\QueryParameter(
                name: 'search',
                description: 'Pencarian berdasarkan tag number',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'BBH-001')
            ),
            new OA\QueryParameter(
                name: 'is_impor',
                description: 'Filter berdasarkan status impor kambing',
                required: false,
                schema: new OA\Schema(type: 'boolean', example: true)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar kambing berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'tag_number', type: 'string', example: '0001'),
                                    new OA\Property(property: 'breed_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'sex', type: 'string', enum: ['male', 'female'], example: 'female'),
                                    new OA\Property(property: 'generation', type: 'string', enum: ['F1', 'F2', 'F3', 'F4', 'F5', 'Pure Breed'], example: 'F1'),
                                    new OA\Property(property: 'birth_date', type: 'string', format: 'date', nullable: true, example: '2025-01-15'),
                                    new OA\Property(property: 'birth_place', type: 'string', nullable: true, example: 'Ajibarang'),
                                    new OA\Property(property: 'life_status', type: 'string', enum: ['alive', 'dead'], example: 'alive'),
                                    new OA\Property(property: 'status', type: 'string', example: 'active'),
                                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Healthy animal'),
                                    new OA\Property(property: 'is_impor', type: 'boolean', example: true),
                                    new OA\Property(property: 'umur', type: 'string', nullable: true, example: '1 Tahun 2 Bulan'),
                                    new OA\Property(property: 'kategori_umur', type: 'string', example: 'dere'),
                                    new OA\Property(
                                        property: 'breed',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'breed_name', type: 'string', example: 'Saanen'),
                                        ],
                                        type: 'object',
                                        nullable: true
                                    ),
                                ],
                                type: 'object'
                            )
                        ),
                        new OA\Property(property: 'per_page', type: 'integer', example: 15),
                        new OA\Property(property: 'total', type: 'integer', example: 25),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/v1/animals',
        tags: ['Animals'],
        summary: 'Menambahkan data kambing baru',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['tag_number', 'breed_id', 'sex', 'generation', 'is_impor'],
                properties: [
                    new OA\Property(property: 'tag_number', type: 'string', maxLength: 1000, example: '0001'),
                    new OA\Property(property: 'breed_id', type: 'integer', example: 1),
                    new OA\Property(property: 'sex', type: 'string', enum: ['male', 'female'], example: 'female'),
                    new OA\Property(property: 'generation', type: 'string', enum: ['F1', 'F2', 'F3', 'F4', 'F5', 'Pure Breed'], example: 'F1'),
                    new OA\Property(property: 'birth_date', type: 'string', format: 'date', nullable: true, example: '2025-01-15'),
                    new OA\Property(property: 'birth_place', type: 'string', nullable: true, example: 'Ajibarang'),
                    new OA\Property(property: 'life_status', type: 'string', enum: ['alive', 'dead'], nullable: true, example: 'alive'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Healthy animal'),
                    new OA\Property(property: 'is_impor', type: 'boolean', example: true),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Hewan berhasil dibuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Animal created.'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'tag_number', type: 'string', example: '0001'),
                                new OA\Property(property: 'breed_id', type: 'integer', example: 1),
                                new OA\Property(property: 'sex', type: 'string', enum: ['male', 'female'], example: 'female'),
                                new OA\Property(property: 'generation', type: 'string', enum: ['F1', 'F2', 'F3', 'F4', 'F5', 'Pure Breed'], example: 'F1'),
                                new OA\Property(property: 'birth_date', type: 'string', format: 'date', nullable: true, example: '2025-01-15'),
                                new OA\Property(property: 'birth_place', type: 'string', nullable: true, example: 'Ajibarang'),
                                new OA\Property(property: 'life_status', type: 'string', enum: ['alive', 'dead'], example: 'alive'),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                                new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Ternak Pertama'),
                                new OA\Property(property: 'is_impor', type: 'boolean', example: true),
                                new OA\Property(property: 'umur', type: 'string', nullable: true, example: '1 Tahun 2 Bulan'),
                                new OA\Property(property: 'kategori_umur', type: 'string', example: 'dere'),
                                new OA\Property(
                                    property: 'breed',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'breed_name', type: 'string', example: 'Saanen'),
                                    ],
                                    type: 'object',
                                    nullable: true
                                ),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            additionalProperties: new OA\AdditionalProperties(
                                type: 'array',
                                items: new OA\Items(type: 'string')
                            )
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/api/v1/animals/{animal}',
        tags: ['Animals'],
        summary: 'Menampilkan detail kambing',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'animal',
                description: 'ID kambing',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'detail kambing berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'tag_number', type: 'string', example: '0001'),
                        new OA\Property(property: 'breed_id', type: 'integer', example: 1),
                        new OA\Property(property: 'sex', type: 'string', enum: ['male', 'female'], example: 'female'),
                        new OA\Property(property: 'generation', type: 'string', enum: ['F1', 'F2', 'F3', 'F4', 'F5', 'Pure Breed'], example: 'F1'),
                        new OA\Property(property: 'birth_date', type: 'string', format: 'date', nullable: true, example: '2025-01-15'),
                        new OA\Property(property: 'birth_place', type: 'string', nullable: true, example: 'Ajibarang'),
                        new OA\Property(property: 'life_status', type: 'string', example: 'alive'),
                        new OA\Property(property: 'status', type: 'string', example: 'active'),
                        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Healthy animal'),
                        new OA\Property(property: 'is_impor', type: 'boolean', example: true),
                        new OA\Property(property: 'umur', type: 'string', nullable: true, example: '1 Tahun 2 Bulan'),
                        new OA\Property(property: 'kategori_umur', type: 'string', example: 'dere'),
                        new OA\Property(
                            property: 'breed',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'breed_name', type: 'string', example: 'Saanen'),
                            ],
                            type: 'object',
                            nullable: true
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Animal not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/animals/{animal}',
        tags: ['Animals'],
        summary: 'Memperbarui data kambing',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(
                name: 'animal',
                description: 'ID kambing',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'tag_number', type: 'string', maxLength: 100, example: 'BBH-001'),
                    new OA\Property(property: 'breed_id', type: 'integer', example: 1),
                    new OA\Property(property: 'sex', type: 'string', enum: ['male', 'female'], example: 'male'),
                    new OA\Property(property: 'generation', type: 'string', enum: ['F1', 'F2', 'F3', 'F4', 'F5', 'Pure Breed'], example: 'F2'),
                    new OA\Property(property: 'birth_date', type: 'string', format: 'date', nullable: true, example: '2025-01-15'),
                    new OA\Property(property: 'birth_place', type: 'string', nullable: true, example: 'Ajibarang'),
                    new OA\Property(property: 'life_status', type: 'string', enum: ['alive', 'dead'], example: 'alive'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Hewan sudah diperbarui'),
                    new OA\Property(property: 'is_impor', type: 'boolean', example: false),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hewan berhasil diperbarui',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Animal updated.'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'tag_number', type: 'string', example: 'BBH-001'),
                                new OA\Property(property: 'breed_id', type: 'integer', example: 1),
                                new OA\Property(property: 'sex', type: 'string', enum: ['male', 'female'], example: 'female'),
                                new OA\Property(property: 'generation', type: 'string', enum: ['F1', 'F2', 'F3', 'F4', 'F5', 'Pure Breed'], example: 'F2'),
                                new OA\Property(property: 'birth_date', type: 'string', format: 'date', nullable: true, example: '2025-01-15'),
                                new OA\Property(property: 'birth_place', type: 'string', nullable: true, example: 'Ajibarang'),
                                new OA\Property(property: 'life_status', type: 'string', enum: ['alive', 'dead'], example: 'alive'),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                                new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Hewan sudah diperbarui'),
                                new OA\Property(property: 'is_impor', type: 'boolean', example: false),
                                new OA\Property(property: 'umur', type: 'string', nullable: true, example: '1 Tahun 2 Bulan'),
                                new OA\Property(property: 'kategori_umur', type: 'string', example: 'Pejantan Muda'),
                                new OA\Property(
                                    property: 'breed',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'breed_name', type: 'string', example: 'Saanen'),
                                    ],
                                    type: 'object',
                                    nullable: true
                                ),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            additionalProperties: new OA\AdditionalProperties(
                                type: 'array',
                                items: new OA\Items(type: 'string')
                            )
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Animal not found'),
        ]
    )]
    public function update(): void {}
}
