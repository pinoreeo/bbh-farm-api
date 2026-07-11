<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class CertificateControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/certificates',
        tags: ['Certificates'],
        summary: 'Menampilkan daftar sertifikat',
        security: [['sanctum' => []]],
        parameters: [
            new OA\QueryParameter(name: 'per_page', description: 'Jumlah data per halaman (1-100)', required: false, schema: new OA\Schema(type: 'integer', example: 15, minimum: 1, maximum: 100)),
            new OA\QueryParameter(name: 'status', description: 'Filter status sertifikat', required: false, schema: new OA\Schema(type: 'string', enum: ['active', 'revoked', 'expired'], example: 'active')),
            new OA\QueryParameter(name: 'include_inactive', description: 'Tampilkan juga data revoked dan expired (0 atau 1)', required: false, schema: new OA\Schema(type: 'integer', enum: [0, 1], example: 0)),
            new OA\QueryParameter(name: 'certificate_type_id', description: 'Filter berdasarkan ID jenis sertifikat', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\QueryParameter(name: 'animal_id', description: 'Filter berdasarkan ID kambing', required: false, schema: new OA\Schema(type: 'integer', example: 5)),
            new OA\QueryParameter(name: 'certificate_number', description: 'Filter berdasarkan nomor sertifikat', required: false, schema: new OA\Schema(type: 'string', example: 'BBH-AKL-2026-0001')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Daftar sertifikat berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/v1/certificates',
        tags: ['Certificates'],
        summary: 'Menerbitkan sertifikat secara otomatis berdasarkan kambing dan jenis sertifikat',
        description: 'Endpoint ini digunakan untuk menerbitkan tiga jenis sertifikat: BIBIT_UNGGUL, KELAHIRAN, dan KEMATIAN. Field death_date dan cause_of_death hanya wajib untuk jenis KEMATIAN.',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['animal_id', 'certificate_type_id'],
                properties: [
                    new OA\Property(
                        property: 'animal_id',
                        type: 'integer',
                        example: 5,
                        description: 'ID kambing yang akan diterbitkan sertifikatnya.'
                    ),
                    new OA\Property(
                        property: 'certificate_type_id',
                        type: 'integer',
                        example: 3,
                        description: 'ID jenis sertifikat. Sesuaikan dengan data pada tabel certificate_types.'
                    ),
                    new OA\Property(
                        property: 'issue_place',
                        type: 'string',
                        nullable: true,
                        example: 'Ajibarang',
                        description: 'Tempat penerbitan sertifikat.'
                    ),
                    new OA\Property(
                        property: 'auto_sign',
                        type: 'boolean',
                        nullable: true,
                        example: true,
                        description: 'Sertifikat otomatis ditandatangani secara digital.'
                    ),
                    new OA\Property(
                        property: 'death_date',
                        type: 'string',
                        format: 'date',
                        nullable: true,
                        example: '2026-04-27',
                        description: 'Tanggal kematian.'
                    ),
                    new OA\Property(
                        property: 'death_time',
                        type: 'string',
                        nullable: true,
                        example: '14:30:00',
                        description: 'Jam kematian. Wajib untuk jenis KEMATIAN.'
                    ),
                    new OA\Property(
                        property: 'cause_of_death',
                        type: 'string',
                        nullable: true,
                        example: 'Sakit',
                        description: 'Penyebab kematian Wajib Di Isi'
                    ),
                ],
                examples: [
                    new OA\Examples(
                        example: 'bibit_unggul',
                        summary: 'sertifikat bibit unggul kambing perah',
                        value: [
                            'animal_id' => 5,
                            'certificate_type_id' => 1,
                            'issue_place' => 'Ajibarang',
                            'auto_sign' => true,
                        ]
                    ),
                    new OA\Examples(
                        example: 'kelahiran',
                        summary: 'Akta Kelahiran',
                        value: [
                            'animal_id' => 6,
                            'certificate_type_id' => 2,
                            'issue_place' => 'Ajibarang',
                            'auto_sign' => true,
                        ]
                    ),
                    new OA\Examples(
                        example: 'kematian',
                        summary: 'Akta Kematian',
                        value: [
                            'animal_id' => 7,
                            'certificate_type_id' => 3,
                            'issue_place' => 'Ajibarang',
                            'auto_sign' => true,
                            'death_date' => '2026-04-27',
                            'death_time' => '14:30:00',
                            'cause_of_death' => 'Sakit',
                        ]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Sertifikat berhasil diterbitkan', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'Validasi bisnis gagal', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/api/v1/certificates/{certificate}',
        tags: ['Certificates'],
        summary: 'Menampilkan detail sertifikat',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'certificate', description: 'ID sertifikat', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail sertifikat berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/Certificate')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/certificates/{certificate}',
        tags: ['Certificates'],
        summary: 'Memperbarui sertifikat',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'certificate', description: 'ID sertifikat', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'issue_place', type: 'string', nullable: true, maxLength: 255, example: 'Ajibarang'),
                    new OA\Property(property: 'valid_from', type: 'string', format: 'date', nullable: true, example: '2026-04-23'),
                    new OA\Property(property: 'valid_until', type: 'string', format: 'date', nullable: true, example: '2027-04-23'),
                    new OA\Property(property: 'death_date', type: 'string', format: 'date', nullable: true, example: '2026-04-27'),
                    new OA\Property(property: 'death_time', type: 'string', nullable: true, example: '14:30:00'),
                    new OA\Property(property: 'cause_of_death', type: 'string', nullable: true, maxLength: 255, example: 'Sakit'),
                    new OA\Property(property: 'auto_sign', type: 'boolean', nullable: true, example: true),
                ],
                example: [
                    'issue_place' => 'Ajibarang',
                    'valid_from' => '2026-04-23',
                    'valid_until' => '2027-04-23',
                    'auto_sign' => true,
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Sertifikat berhasil diperbarui', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate not found'),
        ]
    )]
    public function update(): void {}

    #[OA\Post(
        path: '/api/v1/certificates/{certificate}/revoke',
        tags: ['Certificates'],
        summary: 'Mencabut sertifikat',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'certificate', description: 'ID sertifikat', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['reason'],
                properties: [
                    new OA\Property(property: 'reason', type: 'string', example: 'Data sertifikat salah'),
                    new OA\Property(property: 'revoked_at', type: 'string', format: 'date-time', nullable: true, example: '2026-05-04T12:00:00+07:00'),
                ],
                example: [
                    'reason' => 'Data sertifikat salah',
                    'revoked_at' => '2026-05-04T12:00:00+07:00',
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Sertifikat berhasil dicabut', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'Validation error atau sertifikat sudah revoked', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate not found'),
        ]
    )]
    public function revoke(): void {}

    #[OA\Post(
        path: '/api/v1/certificates/{certificate}/unrevoke',
        tags: ['Certificates'],
        summary: 'Membatalkan pencabutan sertifikat',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'certificate', description: 'ID sertifikat', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Pencabutan sertifikat berhasil dibatalkan', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'Certificate is not revoked', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate not found'),
        ]
    )]
    public function unrevoke(): void {}

    #[OA\Post(
        path: '/api/v1/certificates/{certificate}/sign',
        tags: ['Certificates'],
        summary: 'Menandatangani sertifikat secara digital',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'certificate', description: 'ID sertifikat', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Sertifikat berhasil ditandatangani', content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')),
            new OA\Response(response: 422, description: 'Signing failed', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate not found'),
        ]
    )]
    public function sign(): void {}

    #[OA\Get(
        path: '/api/v1/certificates/{certificate}/print/authenticity',
        tags: ['Certificate Prints'],
        summary: 'Menampilkan halaman cetak sertifikat bibit unggul kambing perah',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'certificate', description: 'ID sertifikat', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Halaman cetak berhasil dibuat', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string', example: '<html>...</html>'))),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate not found'),
        ]
    )]
    public function printAuthenticity(): void {}

    #[OA\Get(
        path: '/api/v1/certificates/{certificate}/print/birth',
        tags: ['Certificate Prints'],
        summary: 'Menampilkan halaman cetak akta kelahiran',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'certificate', description: 'ID sertifikat', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Halaman cetak berhasil dibuat', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string', example: '<html>...</html>'))),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate not found'),
        ]
    )]
    public function printBirth(): void {}

    #[OA\Get(
        path: '/api/v1/certificates/{certificate}/print/death',
        tags: ['Certificate Prints'],
        summary: 'Menampilkan halaman cetak akta kematian',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'certificate', description: 'ID sertifikat', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Halaman cetak berhasil dibuat', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string', example: '<html>...</html>'))),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Certificate not found'),
        ]
    )]
    public function printDeath(): void {}
}
