# Translation Service API

This service manages application translations by key, locale, value, and tags.
It exposes a JSON API for authenticated users to create, search, update, delete,
and export translations.

## Quick Start

Docker is the recommended way to run the service locally.

```sh
cp .env.docker.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app composer dump-autoload
docker compose exec app php artisan db:seed --class=DemoUserSeeder
```

The API will be available at:

```text
http://localhost:8080
```

To seed a larger dataset for testing search and export performance:

```sh
docker compose exec app php artisan db:seed --class=TranslationScaleSeeder
```

The scale seeder creates 100,000 translations across common locales and tags.

## Operating The Service

Common Docker commands:

```sh
docker compose up -d
docker compose logs -f app
docker compose exec app php artisan migrate
docker compose exec app composer dump-autoload
docker compose exec app php artisan db:seed --class=DemoUserSeeder
docker compose exec app php artisan db:seed --class=TranslationScaleSeeder
docker compose exec app vendor/bin/phpunit
docker compose down
```

Useful maintenance commands:

```sh
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan l5-swagger:generate
docker compose exec app composer install
```

If Laravel cannot write logs or cache files, repair permissions with:

```sh
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R ug+rwX storage bootstrap/cache
```

If you need to change host ports, edit these values in `.env`:

```env
APP_PORT=8080
DB_FORWARD_PORT=3307
```

## Local Setup Without Docker

Use this path if you already have PHP, Composer, and MySQL installed.

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
composer dump-autoload
php artisan db:seed --class=DemoUserSeeder
php artisan serve
```

Update `.env` first so the database settings point at your local MySQL server.

If frontend assets are needed:

```sh
npm install
npm run dev
```

## Authentication

Register or log in to get an API token. Protected endpoints require the token in
the `Authorization` header:

```http
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

For local Swagger testing, seed the demo user first:

```sh
docker compose exec app composer dump-autoload
docker compose exec app php artisan db:seed --class=DemoUserSeeder
```

Demo credentials:

```text
email: jane@example.com
password: secret123
initial bearer token: local-demo-api-token
```

You can either authorize Swagger with `local-demo-api-token`, or call
`POST /api/login` with the demo email and password. Login rotates the token, so
use the token returned by the login response after that.

### Register

```sh
curl -X POST http://localhost:8080/api/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Translator",
    "email": "jane@example.com",
    "password": "secret123",
    "password_confirmation": "secret123"
  }'
```

Response includes:

```json
{
  "token": "generated-api-token",
  "user": {
    "id": 1,
    "name": "Jane Translator",
    "email": "jane@example.com"
  }
}
```

### Login

```sh
curl -X POST http://localhost:8080/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "jane@example.com",
    "password": "secret123"
  }'
```

### Current User

```sh
curl http://localhost:8080/api/user \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

### Logout

```sh
curl -X POST http://localhost:8080/api/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

Logout revokes the current token.

## Translation Payload

Create requests can either provide a new key/locale pair:

```json
{
  "key": "auth.login.title",
  "description": "Login page title",
  "locale": "en",
  "locale_name": "English",
  "value": "Log in",
  "is_reviewed": true,
  "tags": ["web", "auth"]
}
```

Or they can reference existing records:

```json
{
  "translation_key_id": 1,
  "locale_id": 1,
  "value": "Log in",
  "is_reviewed": true,
  "tags": ["web", "auth"]
}
```

Rules:

- `value` is required when creating a translation.
- Use either `key` or `translation_key_id`.
- Use either `locale` or `locale_id`.
- Do not send both forms in one request. If `translation_key_id` is present,
  it refers to an existing key and does not create or rename `key`.
- Do not send `locale_name` with `locale_id`; the existing locale record is
  used as-is.
- `tags` is optional and should be an array of strings.
- `is_reviewed` is optional and defaults to `false`.
- Creating a translation for an existing key/locale pair updates that pair.

## Translation API

All translation endpoints require authentication.

### Create Or Upsert A Translation

```sh
curl -X POST http://localhost:8080/api/translations \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{
    "key": "auth.login.title",
    "description": "Login page title",
    "locale": "en",
    "locale_name": "English",
    "value": "Log in",
    "is_reviewed": true,
    "tags": ["web", "auth"]
  }'
```

### Search Translations

```sh
curl "http://localhost:8080/api/translations?q=login&locale=en&tag=auth&per_page=25" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

Supported query parameters:

- `q` searches translation keys and values.
- `locale` filters by locale code, such as `en` or `fr`.
- `key` filters by partial translation key.
- `tag` filters by tag name or slug. Use comma-separated tags like
  `tag=web,auth` when filtering by multiple tags.
- `per_page` controls page size and is capped at 100.

### Show One Translation

```sh
curl http://localhost:8080/api/translations/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

### Update A Translation

```sh
curl -X PUT http://localhost:8080/api/translations/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{
    "value": "Sign in",
    "is_reviewed": true,
    "tags": ["web", "auth", "release"]
  }'
```

Update requests can change `value`, `is_reviewed`, `tags`, `key`,
`translation_key_id`, `locale`, or `locale_id`. If `tags` is included, the
translation's tag list is replaced with the provided array.

PUT requirements:

- Send only the fields you want to change.
- Use either `key` or `translation_key_id`, not both.
- Use either `locale` or `locale_id`, not both.
- The final key/locale pair must not already exist on another translation.
- To update only the translated text, send only `value`.

### Delete A Translation

```sh
curl -X DELETE http://localhost:8080/api/translations/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

Successful deletes return `204 No Content`.

## Export API

Use exports when a client application needs a flat locale dictionary.

```sh
curl "http://localhost:8080/api/translations/export/en" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

Filter exports by tag:

```sh
curl "http://localhost:8080/api/translations/export/en?tag=web" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

Example response:

```json
{
  "locale": "en",
  "count": 2,
  "translations": {
    "auth.login.title": "Log in",
    "auth.logout": "Log out"
  }
}
```

Export responses are cached briefly. Any create, update, or delete operation
invalidates the export cache version.

## API Documentation

Swagger UI loads the checked-in OpenAPI file at `openapi/api-docs.json`.
Before opening Swagger locally, make sure the demo user exists:

```sh
docker compose exec app composer dump-autoload
docker compose exec app php artisan db:seed --class=DemoUserSeeder
```

If Laravel reports `Class DemoUserSeeder does not exist`, run
`docker compose exec app composer dump-autoload` and then run the seed command
again. Laravel 5 loads seeders through Composer's classmap.

Open:

```text
http://localhost:8080/api/documentation
```

If Swagger shows `Failed to load API definition`, clear cached config and retry:

```sh
docker compose exec app php artisan config:clear
```

## Testing

Run the automated test suite:

```sh
docker compose exec app vendor/bin/phpunit
```

For a local install:

```sh
vendor/bin/phpunit
```

Tests use an in-memory SQLite database configured in `phpunit.xml`.

## Design Choices

- Translations are normalized into `translation_keys`, `locales`,
  `translations`, and `tags`. This keeps each key/locale value unique while
  still allowing translations to be grouped by multiple tags.
- The API uses Laravel token authentication for a simple bearer-token flow that
  works well for internal tools and scripts.
- Controllers handle HTTP concerns only. `TranslationService` owns application
  behavior, while `TranslationRepository` owns database queries and persistence.
- Search joins the key and locale tables so filters can target human-readable
  fields such as locale code, translation key, value text, and tag slug.
- Exports return a flat key/value object because that is the easiest shape for
  frontend translation loaders to consume.
