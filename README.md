# Financio Backend — Laravel Invoice Management API

REST API for invoice management built with Laravel 11, featuring full CRUD operations, soft deletes, and PHPUnit test coverage.

---

## Requirements

- PHP >= 8.2
- Composer
- Docker Desktop (for MySQL)

---

## Tech Stack

- **Framework** — Laravel 11
- **Database** — MySQL 8.0 (via Docker)
- **Testing** — PHPUnit with SQLite in-memory
- **API** — RESTful API with Laravel API Resources

---

## Setup Instructions

### 1. Clone the repository

```bash
git clone <repository-url>
cd financio-backend
```

### 2. Install dependencies

```bash
composer install
```

### 3. Environment setup

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Update the following values in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=financio_backend
DB_USERNAME=root
DB_PASSWORD=secret
```

Then generate the application key:

```bash
php artisan key:generate
```

### 4. Start MySQL via Docker

```bash
docker-compose up -d
```

Starts a MySQL 8.0 container on port `3307`. Verify:

```bash
docker ps
```

### 5. Run migrations

```bash
php artisan migrate
```

### 6. Run seeders

```bash
php artisan db:seed
```

Populates the database with sample invoices and invoice items.

### 7. Start development server

```bash
php artisan serve
```

API available at: `http://localhost:8000`

---

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/invoices` | List all invoices (paginated) |
| POST | `/api/invoices` | Create a new invoice |
| GET | `/api/invoices/{id}` | Get invoice details with items |
| PUT | `/api/invoices/{id}` | Update an invoice |
| DELETE | `/api/invoices/{id}` | Soft delete an invoice |


---

## Running PHPUnit Tests

Tests use **SQLite in-memory** — no additional setup required.

### Run all tests

```bash
php artisan test
```

### Run invoice API tests only

```bash
php artisan test --filter=InvoiceApiTest
```

### Expected output

```
PASS  Tests\Feature\InvoiceApiTest
✓ can list invoices
✓ list invoices returns empty when no data
✓ list invoices supports pagination
✓ list invoices fails with invalid sort
✓ can create invoice
✓ create invoice calculates amount automatically
✓ create invoice fails without required fields
✓ create invoice fails without items
✓ create invoice fails with invalid item fields
✓ create invoice fails duplicate number same customer same year
✓ create invoice allows same number different customer
✓ can show invoice with items
✓ show invoice returns 404 when not found
✓ can update invoice
✓ update invoice replaces old items
✓ update invoice fails without required fields
✓ update invoice allows same invoice number on itself
✓ update invoice returns 404 when not found
✓ can delete invoice
✓ deleted invoice not visible in list
✓ deleted invoice returns 404 on show
✓ delete invoice returns 404 when not found

Tests: 22 passed
```


