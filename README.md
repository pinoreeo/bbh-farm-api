# BBH Farm API

Laravel backend API for livestock management, digital certificate issuance, and RSA-SHA256 certificate verification at BBH Farm.

## Main Features

### Livestock Management

- Animal identity and breed management
- Colony pen management
- Breeding period and breeding female records
- Pregnancy check records
- Birth event and offspring birth records
- Postnatal care records
- Weight records
- Health treatment records
- Vaccination records

### Digital Certificate Management

- Issue digital certificates for livestock records
- Supported certificate types:
  - Superior Livestock Certificate
  - Livestock Birth Certificate
  - Livestock Death Certificate
- RSA-SHA256 digital signing
- RSA key generation and activation
- Certificate revocation and reactivation
- Certificate preview and PDF export
- QR/token-based certificate verification

### Public Verification

- Verify certificate by certificate number
- Verify certificate by QR/token
- Verify official certificate PDF integrity
- Public verification logs
- Rate limiting for public verification endpoints

### Security and Audit

- Laravel Sanctum token authentication
- Admin-only protected API routes
- Admin activity logging
- Structured validation responses
- Soft-deactivation patterns for operational records

## Tech Stack

- PHP 8.2+
- Laravel 11
- Laravel Sanctum
- MySQL or another Laravel-supported relational database
- OpenSSL
- Simple QrCode
- L5 Swagger / OpenAPI
- PHPUnit
- PHPStan / Larastan
- Laravel Pint

## Installation Requirements

Make sure the following tools are installed:

- PHP 8.2 or newer
- Composer
- MySQL, MariaDB, or another supported database
- Required PHP extensions:
  - `openssl`
  - `mbstring`
  - `xml`
  - `pdo`
  - database driver extension, for example `pdo_mysql`

## Installation

Clone the repository:

```bash
git clone https://github.com/USERNAME/bbh-farm-api.git
cd bbh-farm-api
```

Install dependencies:

```bash
composer install
```

Create the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure your database in `.env`, then run migrations and seeders:

```bash
php artisan migrate --seed
```

Start the local development server:

```bash
php artisan serve
```

The API will be available at:

```text
http://127.0.0.1:8000/api/v1
```

## API Documentation

Generate Swagger/OpenAPI documentation:

```bash
php artisan l5-swagger:generate
```

Open the documentation page:

```text
http://127.0.0.1:8000/api/documentation
```

## Project Structure

```text
app/
  Http/
    Controllers/Api/V1/
    Middleware/
  Models/
  OpenApi/
  Services/

config/
database/
  migrations/
  seeders/

public/
  images/

resources/
  views/certificates/

routes/
  api.php

tests/
```

Important directories:

- `app/Http/Controllers/Api/V1` contains API controllers.
- `app/Models` contains Eloquent models.
- `app/Services` contains business logic for signing, verification, PDF integrity, and admin activity logging.
- `app/OpenApi` contains OpenAPI documentation classes.
- `database/migrations` contains database schema definitions.
- `database/seeders` contains initial data seeders.
- `resources/views/certificates` contains certificate PDF templates.
- `public/images` contains certificate assets such as logo and signature images.
- `tests` contains automated tests.

## Example Usage

### Login

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@farm.com",
    "password": "admin",
    "device_name": "local-client"
  }'
```

The response contains an access token. Use the token for protected admin endpoints:

```bash
curl http://127.0.0.1:8000/api/v1/auth/me \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### Create Animal

```bash
curl -X POST http://127.0.0.1:8000/api/v1/animals \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "tag_number": "BBH-001",
    "breed_id": 1,
    "sex": "female",
    "generation": "F1",
    "birth_date": "2024-01-01",
    "birth_place": "Ajibarang",
    "life_status": "alive",
    "is_impor": false
  }'
```

### Verify Certificate by Number

```bash
curl -X POST http://127.0.0.1:8000/api/v1/public/certificates/verify \
  -H "Content-Type: application/json" \
  -d '{
    "certificate_number": "BBH-SBU-2026-0001"
  }'
```

### Verify Certificate by Public Token

```bash
curl http://127.0.0.1:8000/api/v1/public/certificates/verify/YOUR_VERIFICATION_TOKEN
```

### Verify Certificate PDF

```bash
curl -X POST http://127.0.0.1:8000/api/v1/public/certificates/verify-pdf \
  -F "certificate_number=BBH-SBU-2026-0001" \
  -F "pdf=@certificate.pdf"
```

## Certificate Verification Flow

1. Admin issues a certificate.
2. The system builds a canonical certificate payload.
3. The payload is hashed using SHA-256.
4. The hash is digitally signed using the active RSA private key.
5. The public verification endpoint checks payload integrity, certificate status, and RSA signature validity.
6. Users can verify the certificate by certificate number, QR/token, or official PDF upload.

## Testing

Run the automated test suite:

```bash
php artisan test
```

Run static analysis:

```bash
vendor/bin/phpstan analyse
```

Run Laravel Pint:

```bash
vendor/bin/pint
```

## Contributing

This project is maintained by the repository owner. Suggestions and issue reports are welcome through GitHub Issues.
