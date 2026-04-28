#!/usr/bin/env sh
set -eu

docker compose exec app php artisan "$@"

