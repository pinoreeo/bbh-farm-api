<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class AuthControllerDocumentation
{
    #[OA\Post(
        path: '/api/v1/auth/login',
        tags: ['Auth'],
        summary: 'Login user dan menghasilkan access token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'admin@farm.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'admin'
                    ),
                    new OA\Property(
                        property: 'device_name',
                        type: 'string',
                        nullable: true,
                        maxLength: 100,
                        example: 'Postman'
                    ),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Login success.'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                        new OA\Property(property: 'access_token', type: 'string', example: '1|abcdefghijklmno123456789'),
                        new OA\Property(
                            property: 'user',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Rio'),
                                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@farm.com'),
                                new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true, example: '2026-04-10T08:00:00.000000Z'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-10T08:00:00.000000Z'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-04-10T08:00:00.000000Z'),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validasi gagal atau kredensial salah',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'email',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['Invalid credentials.']
                                ),
                            ]
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function login(): void {}

    #[OA\Get(
        path: '/api/v1/auth/me',
        tags: ['Auth'],
        summary: 'Menampilkan data user yang sedang login',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data user berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Admin User'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@example.com'),
                        new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true, example: '2026-04-10T08:00:00.000000Z'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-10T08:00:00.000000Z'),
                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-04-10T08:00:00.000000Z'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function me(): void {}

    #[OA\Post(
        path: '/api/v1/auth/logout',
        tags: ['Auth'],
        summary: 'Logout user dan menghapus token aktif saat ini',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Logged out.'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function logout(): void {}
}
