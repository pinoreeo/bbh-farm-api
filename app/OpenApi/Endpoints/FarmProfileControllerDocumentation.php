<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class FarmProfileControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/farm',
        tags: ['Farm Profile'],
        summary: 'Menampilkan profil farm',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil farm berhasil diambil',
                content: new OA\JsonContent(ref: '#/components/schemas/Farm')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Farm not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/api/v1/farm',
        tags: ['Farm Profile'],
        summary: 'Memperbarui profil farm',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'farm_name', type: 'string', maxLength: 255, example: 'BBH Farm Kambing Perah'),
                    new OA\Property(property: 'address', type: 'string', nullable: true, example: 'Jl. Contoh No. 1, Ajibarang'),
                    new OA\Property(property: 'phone', type: 'string', nullable: true, maxLength: 100, example: '081234567890'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, maxLength: 255, example: 'farm@example.com'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil farm berhasil diperbarui',
                content: new OA\JsonContent(ref: '#/components/schemas/ResourceResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Farm not found'),
        ]
    )]
    public function update(): void {}
}
