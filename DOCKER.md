# Docker

This project includes a Docker Compose setup for local development.

## Start

```sh
docker compose up -d --build
```

The app will be available at `http://localhost:8080` by default.

## First Run

```sh
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed --class=TranslationScaleSeeder
```

The scale seeder creates 100,000 translations.

## Useful Commands

```sh
docker compose exec app php artisan test
docker compose exec app php artisan l5-swagger:generate
docker compose exec app composer install
docker compose logs -f app
docker compose down
```

Use `APP_PORT` or `DB_FORWARD_PORT` in `.env` if you need different host ports.

