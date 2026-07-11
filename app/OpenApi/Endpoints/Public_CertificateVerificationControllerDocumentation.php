<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class Public_CertificateVerificationControllerDocumentation
{
    #[OA\Get(
        path: '/api/v1/public/certificates/{certificate_number}',
        summary: 'Get public certificate detail',
        tags: ['Public Certificate'],
        parameters: [
            new OA\Parameter(
                name: 'certificate_number',
                in: 'path',
                required: true,
                description: 'Certificate number',
                schema: new OA\Schema(type: 'string', example: 'BBH-AKL-2026-0001')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Certificate found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'certificate_number', type: 'string', example: 'BBH-AKL-2026-0001'),
                        new OA\Property(property: 'status', type: 'string', example: 'active'),
                        new OA\Property(property: 'issue_date', type: 'string', format: 'date', example: '2026-04-23'),
                        new OA\Property(property: 'issue_place', type: 'string', nullable: true, example: 'Ajibarang'),
                        new OA\Property(property: 'certificate_type', type: 'string', nullable: true, example: 'KELAHIRAN'),
                        new OA\Property(
                            property: 'animal',
                            type: 'object',
                            nullable: true,
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 6),
                                new OA\Property(property: 'tag_number', type: 'string', example: '25201'),
                                new OA\Property(property: 'sex', type: 'string', example: 'female'),
                                new OA\Property(property: 'generation', type: 'string', example: 'F3'),
                                new OA\Property(property: 'life_status', type: 'string', example: 'alive'),
                            ]
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Certificate not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Certificate not found.'),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function showPublic(): void {}

    #[OA\Post(
        path: '/api/v1/public/certificates/verify',
        summary: 'Verify certificate authenticity and validity by certificate number',
        tags: ['Public Certificate'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['certificate_number'],
                properties: [
                    new OA\Property(
                        property: 'certificate_number',
                        type: 'string',
                        maxLength: 150,
                        example: 'BBH-AKL-2026-0001'
                    ),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Verification result',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'certificate_number', type: 'string', example: 'BBH-AKL-2026-0001'),
                        new OA\Property(property: 'verification_token', type: 'string', nullable: true, example: '550e8400-e29b-41d4-a716-446655440000'),
                        new OA\Property(property: 'certificate_status', type: 'string', example: 'active'),
                        new OA\Property(property: 'is_authentic', type: 'boolean', example: true),
                        new OA\Property(property: 'authenticity_reason', type: 'string', nullable: true, example: null),
                        new OA\Property(property: 'is_valid', type: 'boolean', example: true),
                        new OA\Property(property: 'reason', type: 'string', nullable: true, example: null),
                        new OA\Property(property: 'certificate_type', type: 'string', nullable: true, example: 'KELAHIRAN'),
                        new OA\Property(
                            property: 'animal',
                            type: 'object',
                            nullable: true,
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 6),
                                new OA\Property(property: 'tag_number', type: 'string', example: '25201'),
                                new OA\Property(property: 'sex', type: 'string', example: 'female'),
                                new OA\Property(property: 'generation', type: 'string', example: 'F3'),
                                new OA\Property(property: 'life_status', type: 'string', example: 'alive'),
                            ]
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Certificate not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Certificate not found.'),
                        new OA\Property(property: 'is_valid', type: 'boolean', example: false),
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
        ]
    )]
    public function verify(): void {}

    #[OA\Get(
        path: '/api/v1/public/certificates/verify/{token}',
        summary: 'Verify certificate authenticity and validity by QR token',
        tags: ['Public Certificate'],
        parameters: [
            new OA\Parameter(
                name: 'token',
                in: 'path',
                required: true,
                description: 'Verification token from QR code',
                schema: new OA\Schema(type: 'string', example: '550e8400-e29b-41d4-a716-446655440000')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Verification result',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'certificate_number', type: 'string', example: 'BBH-AKL-2026-0001'),
                        new OA\Property(property: 'verification_token', type: 'string', nullable: true, example: '550e8400-e29b-41d4-a716-446655440000'),
                        new OA\Property(property: 'certificate_status', type: 'string', example: 'active'),
                        new OA\Property(property: 'is_authentic', type: 'boolean', example: true),
                        new OA\Property(property: 'authenticity_reason', type: 'string', nullable: true, example: null),
                        new OA\Property(property: 'is_valid', type: 'boolean', example: true),
                        new OA\Property(property: 'reason', type: 'string', nullable: true, example: null),
                        new OA\Property(property: 'certificate_type', type: 'string', nullable: true, example: 'KELAHIRAN'),
                        new OA\Property(
                            property: 'animal',
                            type: 'object',
                            nullable: true,
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 6),
                                new OA\Property(property: 'tag_number', type: 'string', example: '25201'),
                                new OA\Property(property: 'sex', type: 'string', example: 'female'),
                                new OA\Property(property: 'generation', type: 'string', example: 'F3'),
                                new OA\Property(property: 'life_status', type: 'string', example: 'alive'),
                            ]
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Certificate not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Certificate not found.'),
                        new OA\Property(property: 'is_valid', type: 'boolean', example: false),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function verifyByToken(): void {}
}
