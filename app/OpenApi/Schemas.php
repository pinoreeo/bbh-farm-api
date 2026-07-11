<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MessageResponse',
    type: 'object',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Operation completed.'),
    ]
)]
#[OA\Schema(
    schema: 'ValidationErrorResponse',
    type: 'object',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string')
            ),
            example: ['field' => ['The field is required.']]
        ),
    ]
)]
#[OA\Schema(
    schema: 'PaginationLink',
    type: 'object',
    properties: [
        new OA\Property(property: 'url', type: 'string', nullable: true, example: 'http://127.0.0.1:8000/api/v1/breeds?page=1'),
        new OA\Property(property: 'label', type: 'string', example: '1'),
        new OA\Property(property: 'active', type: 'boolean', example: true),
    ]
)]
#[OA\Schema(
    schema: 'PaginatedResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
        new OA\Property(property: 'first_page_url', type: 'string', example: 'http://127.0.0.1:8000/api/v1/resources?page=1'),
        new OA\Property(property: 'from', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'last_page', type: 'integer', example: 1),
        new OA\Property(property: 'last_page_url', type: 'string', example: 'http://127.0.0.1:8000/api/v1/resources?page=1'),
        new OA\Property(property: 'links', type: 'array', items: new OA\Items(ref: '#/components/schemas/PaginationLink')),
        new OA\Property(property: 'next_page_url', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'path', type: 'string', example: 'http://127.0.0.1:8000/api/v1/resources'),
        new OA\Property(property: 'per_page', type: 'integer', example: 15),
        new OA\Property(property: 'prev_page_url', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'to', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'total', type: 'integer', example: 1),
    ]
)]
#[OA\Schema(
    schema: 'Breed',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'breed_name', type: 'string', example: 'Saanen'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Ras kambing perah untuk program pembibitan'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-04T12:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-05-04T12:00:00.000000Z'),
    ]
)]
#[OA\Schema(
    schema: 'Animal',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'tag_number', type: 'string', example: 'BBH-001'),
        new OA\Property(property: 'breed_id', type: 'integer', example: 1),
        new OA\Property(property: 'sex', type: 'string', enum: ['male', 'female'], example: 'female'),
        new OA\Property(property: 'generation', type: 'string', example: 'F1'),
        new OA\Property(property: 'birth_date', type: 'string', format: 'date', nullable: true, example: '2025-01-15'),
        new OA\Property(property: 'birth_place', type: 'string', nullable: true, example: 'Ajibarang'),
        new OA\Property(property: 'life_status', type: 'string', enum: ['alive', 'dead'], example: 'alive'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Healthy animal'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
        new OA\Property(property: 'is_impor', type: 'boolean', example: false),
        new OA\Property(property: 'umur', type: 'string', nullable: true, example: '1 Tahun 2 Bulan'),
        new OA\Property(property: 'kategori_umur', type: 'string', example: 'dere'),
        new OA\Property(property: 'breed', ref: '#/components/schemas/Breed', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-04T12:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-05-04T12:00:00.000000Z'),
    ]
)]
#[OA\Schema(
    schema: 'Farm',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'farm_name', type: 'string', example: 'BBH Farm Kambing Perah'),
        new OA\Property(property: 'address', type: 'string', nullable: true, example: 'Jl. Contoh No. 1, Ajibarang'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '081234567890'),
        new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'farm@example.com'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-04T12:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-05-04T12:00:00.000000Z'),
    ]
)]
#[OA\Schema(
    schema: 'ColonyPen',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'pen_code', type: 'string', example: 'KDG-A1'),
        new OA\Property(property: 'colony_type', type: 'string', example: 'koloni_kawin'),
        new OA\Property(property: 'location', type: 'string', nullable: true, example: 'Blok A'),
        new OA\Property(property: 'capacity', type: 'integer', nullable: true, example: 20),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
    ]
)]
#[OA\Schema(
    schema: 'BreedingPeriod',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'colony_pen_id', type: 'integer', example: 1),
        new OA\Property(property: 'period_code', type: 'string', example: 'BP-2026-001'),
        new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2026-04-01'),
        new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true, example: '2026-05-01'),
        new OA\Property(property: 'male_animal_id', type: 'integer', example: 2),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Periode kawin April'),
        new OA\Property(property: 'colony_pen', ref: '#/components/schemas/ColonyPen', nullable: true),
        new OA\Property(property: 'male_animal', ref: '#/components/schemas/Animal', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'BreedingFemale',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'breeding_period_id', type: 'integer', example: 1),
        new OA\Property(property: 'female_animal_id', type: 'integer', example: 3),
        new OA\Property(property: 'entry_date', type: 'string', format: 'date', example: '2026-04-01'),
        new OA\Property(property: 'exit_date', type: 'string', format: 'date', nullable: true, example: null),
        new OA\Property(property: 'exit_reason', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
        new OA\Property(property: 'breeding_period', ref: '#/components/schemas/BreedingPeriod', nullable: true),
        new OA\Property(property: 'female_animal', ref: '#/components/schemas/Animal', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'PregnancyCheck',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'breeding_period_id', type: 'integer', example: 1),
        new OA\Property(property: 'female_animal_id', type: 'integer', example: 3),
        new OA\Property(property: 'check_date', type: 'string', format: 'date', example: '2026-04-20'),
        new OA\Property(property: 'is_pregnant', type: 'boolean', example: true),
        new OA\Property(property: 'method', type: 'string', nullable: true, example: 'palpasi'),
        new OA\Property(property: 'estimated_gestation_days', type: 'integer', nullable: true, example: 30),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Kondisi baik'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
    ]
)]
#[OA\Schema(
    schema: 'BirthEvent',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'dam_id', type: 'integer', example: 3),
        new OA\Property(property: 'sire_id', type: 'integer', nullable: true, example: 2),
        new OA\Property(property: 'birth_date', type: 'string', format: 'date', example: '2026-04-10'),
        new OA\Property(property: 'birth_time', type: 'string', nullable: true, example: '08:30:00'),
        new OA\Property(property: 'offspring_count', type: 'integer', example: 2),
        new OA\Property(property: 'birth_process', type: 'string', example: 'normal'),
        new OA\Property(property: 'dam_grade', type: 'string', nullable: true, example: 'A'),
        new OA\Property(property: 'birth_place', type: 'string', nullable: true, example: 'Kandang A'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Kelahiran normal'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
        new OA\Property(property: 'dam', ref: '#/components/schemas/Animal', nullable: true),
        new OA\Property(property: 'sire', ref: '#/components/schemas/Animal', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'OffspringBirth',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'birth_event_id', type: 'integer', example: 1),
        new OA\Property(property: 'offspring_animal_id', type: 'integer', example: 6),
        new OA\Property(property: 'birth_weight_kg', type: 'number', format: 'float', example: 1.25),
        new OA\Property(property: 'birth_status', type: 'string', enum: ['alive', 'dead'], example: 'alive'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Anak sehat'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
    ]
)]
#[OA\Schema(
    schema: 'PostnatalCareRecord',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'birth_event_id', type: 'integer', example: 1),
        new OA\Property(property: 'target_animal_id', type: 'integer', example: 6),
        new OA\Property(property: 'care_date', type: 'string', format: 'date', example: '2026-04-10'),
        new OA\Property(property: 'administration_method', type: 'string', nullable: true, example: 'DOT'),
        new OA\Property(property: 'volume_ml', type: 'number', format: 'float', nullable: true, example: 250),
        new OA\Property(property: 'navel_iodine_status', type: 'string', nullable: true, example: 'Ok'),
        new OA\Property(property: 'vitamin_ade_ml', type: 'number', format: 'float', nullable: true, example: 2.5),
        new OA\Property(property: 'vitamin_b_complex_ml', type: 'number', format: 'float', nullable: true, example: 2.5),
        new OA\Property(property: 'intracin_ml', type: 'number', format: 'float', nullable: true, example: 5),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Respon baik'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
    ]
)]
#[OA\Schema(
    schema: 'WeightRecord',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'animal_id', type: 'integer', example: 6),
        new OA\Property(property: 'record_date', type: 'string', format: 'date', example: '2026-04-10'),
        new OA\Property(property: 'weight_kg', type: 'number', format: 'float', example: 2.9),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Bobot meningkat'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
    ]
)]
#[OA\Schema(
    schema: 'HealthTreatment',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'animal_id', type: 'integer', example: 6),
        new OA\Property(property: 'treatment_group', type: 'string', example: 'antibiotic'),
        new OA\Property(property: 'product_name', type: 'string', example: 'Amoxicillin'),
        new OA\Property(property: 'treatment_date', type: 'string', format: 'date', example: '2026-04-10'),
        new OA\Property(property: 'dosage', type: 'string', nullable: true, example: '1 ml'),
        new OA\Property(property: 'administration_route', type: 'string', nullable: true, example: 'oral'),
        new OA\Property(property: 'action_category', type: 'string', nullable: true, example: 'treatment'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Nafsu makan membaik'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
    ]
)]
#[OA\Schema(
    schema: 'Vaccination',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'animal_id', type: 'integer', example: 6),
        new OA\Property(property: 'category_name', type: 'string', example: 'PMK'),
        new OA\Property(property: 'vaccination_date', type: 'string', format: 'date', example: '2026-04-10'),
        new OA\Property(property: 'product_name', type: 'string', example: 'Vaksin Enterotoxemia'),
        new OA\Property(property: 'dosage', type: 'string', nullable: true, example: '1 ml'),
        new OA\Property(property: 'administration_route', type: 'string', nullable: true, example: 'subcutaneous'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Tidak ada reaksi'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
    ]
)]
#[OA\Schema(
    schema: 'CertificateType',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'type_code', type: 'string', example: 'KELAHIRAN'),
        new OA\Property(property: 'type_name', type: 'string', nullable: true, example: 'Akta Kelahiran'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Sertifikat kelahiran kambing'),
        new OA\Property(property: 'template_version', type: 'string', nullable: true, example: 'v1'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
    ]
)]
#[OA\Schema(
    schema: 'Certificate',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'animal_id', type: 'integer', example: 6),
        new OA\Property(property: 'certificate_type_id', type: 'integer', example: 2),
        new OA\Property(property: 'certificate_number', type: 'string', example: 'BBH-AKL-2026-0001'),
        new OA\Property(property: 'verification_token', type: 'string', nullable: true, example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'issue_date', type: 'string', format: 'date', example: '2026-04-23'),
        new OA\Property(property: 'issue_place', type: 'string', nullable: true, example: 'Ajibarang'),
        new OA\Property(property: 'birth_event_id', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'valid_from', type: 'string', format: 'date', nullable: true, example: '2026-04-23'),
        new OA\Property(property: 'valid_until', type: 'string', format: 'date', nullable: true, example: null),
        new OA\Property(property: 'death_date', type: 'string', format: 'date', nullable: true, example: null),
        new OA\Property(property: 'death_time', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'cause_of_death', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'barcode_value', type: 'string', nullable: true, example: 'http://127.0.0.1:8000/api/v1/public/certificates/verify/550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'barcode_format', type: 'string', nullable: true, example: 'qrcode'),
        new OA\Property(property: 'canonical_method', type: 'string', nullable: true, example: 'canonical-json'),
        new OA\Property(property: 'hash_sha256', type: 'string', nullable: true, example: 'a9f5f6e7b4c8d2e0a1b3c5d7e9f00112233445566778899aabbccddeeff0011'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'revoked', 'expired'], example: 'active'),
    ]
)]
#[OA\Schema(
    schema: 'RsaKey',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'key_identifier', type: 'string', nullable: true, example: 'BBH-RSA-2026-001'),
        new OA\Property(property: 'public_key_pem', type: 'string', nullable: true, example: '-----BEGIN PUBLIC KEY-----...-----END PUBLIC KEY-----'),
        new OA\Property(property: 'algorithm', type: 'string', example: 'RSA'),
        new OA\Property(property: 'key_length', type: 'integer', nullable: true, example: 2048),
        new OA\Property(property: 'fingerprint_sha256', type: 'string', nullable: true, example: '2c26b46b68ffc68ff99b453c1d30413413422d706483bfa0f98a5e886266e7ae'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
    ]
)]
#[OA\Schema(
    schema: 'CertificateSignature',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'certificate_id', type: 'integer', example: 1),
        new OA\Property(property: 'rsa_key_id', type: 'integer', example: 1),
        new OA\Property(property: 'signature_scheme', type: 'string', example: 'RSA-SHA256'),
        new OA\Property(property: 'signature_base64', type: 'string', example: 'MEUCIQ...'),
        new OA\Property(property: 'signed_at', type: 'string', format: 'date-time', nullable: true, example: '2026-05-04T12:00:00.000000Z'),
        new OA\Property(property: 'status', type: 'string', example: 'active'),
    ]
)]
#[OA\Schema(
    schema: 'CertificateRevocation',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'certificate_id', type: 'integer', example: 1),
        new OA\Property(property: 'revoked_at', type: 'string', format: 'date-time', nullable: true, example: '2026-05-04T12:00:00.000000Z'),
        new OA\Property(property: 'reason', type: 'string', nullable: true, example: 'Data sertifikat salah'),
    ]
)]
#[OA\Schema(
    schema: 'CertificateVerificationLog',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'certificate_id', type: 'integer', example: 1),
        new OA\Property(property: 'verification_time', type: 'string', format: 'date-time', nullable: true, example: '2026-05-04T12:00:00.000000Z'),
        new OA\Property(property: 'is_valid', type: 'boolean', example: true),
        new OA\Property(property: 'certificate_status_at_verification', type: 'string', nullable: true, example: 'active'),
        new OA\Property(property: 'failure_reason', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'used_key_fingerprint', type: 'string', nullable: true, example: '2c26b46b68ffc68ff99b453c1d30413413422d706483bfa0f98a5e886266e7ae'),
        new OA\Property(property: 'used_barcode_value', type: 'string', nullable: true, example: 'http://127.0.0.1:8000/api/v1/public/certificates/verify/550e8400-e29b-41d4-a716-446655440000'),
    ]
)]
#[OA\Schema(
    schema: 'ResourceResponse',
    type: 'object',
    required: ['message', 'data'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Resource created.'),
        new OA\Property(property: 'data', type: 'object'),
    ]
)]
#[OA\Schema(
    schema: 'CertificateVerificationResult',
    type: 'object',
    properties: [
        new OA\Property(property: 'certificate_number', type: 'string', example: 'BBH-AKL-2026-0001'),
        new OA\Property(property: 'verification_token', type: 'string', nullable: true, example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'certificate_status', type: 'string', example: 'active'),
        new OA\Property(property: 'is_authentic', type: 'boolean', example: true),
        new OA\Property(property: 'authenticity_reason', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'is_valid', type: 'boolean', example: true),
        new OA\Property(property: 'reason', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'certificate_type', type: 'string', nullable: true, example: 'KELAHIRAN'),
        new OA\Property(property: 'animal', ref: '#/components/schemas/Animal', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'CertificateQrResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'certificate_id', type: 'integer', example: 1),
        new OA\Property(property: 'qr_base64', type: 'string', example: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0i...'),
        new OA\Property(property: 'url', type: 'string', example: 'http://127.0.0.1:8000/api/v1/public/certificates/verify/550e8400-e29b-41d4-a716-446655440000'),
    ]
)]
final class Schemas {}
