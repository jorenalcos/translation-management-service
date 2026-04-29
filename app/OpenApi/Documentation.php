<?php

namespace App\OpenApi;

/**
 * @OA\Info(
 *     title="Translation Service API",
 *     version="1.0.0",
 *     description="API for authenticated translation CRUD, search, tagging, and locale export. For local Swagger testing, run `php artisan db:seed --class=DemoUserSeeder`, then log in with jane@example.com / secret123 or authorize with bearer token local-demo-api-token."
 * )
 *
 * @OA\Server(
 *     url="/",
 *     description="Application server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="API token"
 * )
 *
 * @OA\Schema(
 *     schema="TranslationPayload",
 *     description="Create or upsert a translation. Use either key/locale or translation_key_id/locale_id, not both.",
 *     required={"key", "locale", "value"},
 *     @OA\Property(property="key", type="string", description="Use this when creating or resolving by key text. Do not send with translation_key_id.", example="auth.login.title"),
 *     @OA\Property(property="translation_key_id", type="integer", description="Use this when referencing an existing translation key. Do not send with key.", example=1),
 *     @OA\Property(property="description", type="string", nullable=true, example="Login page title"),
 *     @OA\Property(property="locale", type="string", description="Use this when creating or resolving by locale code. Do not send with locale_id.", example="en"),
 *     @OA\Property(property="locale_id", type="integer", description="Use this when referencing an existing locale. Do not send with locale or locale_name.", example=1),
 *     @OA\Property(property="locale_name", type="string", description="Only used when locale is provided and a new locale must be created.", example="English"),
 *     @OA\Property(property="value", type="string", example="Log in"),
 *     @OA\Property(property="is_reviewed", type="boolean", example=true),
 *     @OA\Property(
 *         property="tags",
 *         type="array",
 *         @OA\Items(type="string"),
 *         example={"web", "auth"}
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="TranslationUpdatePayload",
 *     description="Partial update payload. Send only the fields that should change. Use either key or translation_key_id, and either locale or locale_id. The resulting key/locale pair must not already exist on another translation.",
 *     @OA\Property(property="key", type="string", example="auth.login.title"),
 *     @OA\Property(property="translation_key_id", type="integer", example=1),
 *     @OA\Property(property="locale", type="string", example="en"),
 *     @OA\Property(property="locale_id", type="integer", example=1),
 *     @OA\Property(property="value", type="string", example="Sign in"),
 *     @OA\Property(property="is_reviewed", type="boolean", example=true),
 *     @OA\Property(
 *         property="tags",
 *         type="array",
 *         @OA\Items(type="string"),
 *         example={"web", "auth", "release"}
 *     )
 * )
 *
 * @OA\Post(
 *     path="/api/register",
 *     tags={"Auth"},
 *     summary="Register a new API user",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "password", "password_confirmation"},
 *             @OA\Property(property="name", type="string", example="Jane Translator"),
 *             @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="secret123"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Registered user and token"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Post(
 *     path="/api/login",
 *     tags={"Auth"},
 *     summary="Issue an API token",
 *     description="For local Swagger testing, seed the demo user first: `php artisan db:seed --class=DemoUserSeeder`. Demo credentials: jane@example.com / secret123.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="secret123")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Authenticated token"),
 *     @OA\Response(response=422, description="Invalid credentials")
 * )
 *
 * @OA\Get(
 *     path="/api/user",
 *     tags={"Auth"},
 *     summary="Fetch the current user",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=200, description="Current user"),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Post(
 *     path="/api/logout",
 *     tags={"Auth"},
 *     summary="Revoke the current token",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=204, description="Token revoked"),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Get(
 *     path="/api/translations",
 *     tags={"Translations"},
 *     summary="Search translations",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
 *     @OA\Parameter(name="locale", in="query", @OA\Schema(type="string")),
 *     @OA\Parameter(name="key", in="query", @OA\Schema(type="string")),
 *     @OA\Parameter(name="tag", in="query", @OA\Schema(type="string")),
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", maximum=100)),
 *     @OA\Response(response=200, description="Paginated translations"),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Post(
 *     path="/api/translations",
 *     tags={"Translations"},
 *     summary="Create or upsert a translation",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TranslationPayload")),
 *     @OA\Response(response=201, description="Created translation"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Get(
 *     path="/api/translations/{translation}",
 *     tags={"Translations"},
 *     summary="Show one translation",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="translation", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Translation"),
 *     @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Put(
 *     path="/api/translations/{translation}",
 *     tags={"Translations"},
 *     summary="Update a translation",
 *     description="Partially update a translation. If key or locale is changed, the resulting key/locale pair must be unique.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="translation", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TranslationUpdatePayload")),
 *     @OA\Response(response=200, description="Updated translation"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Delete(
 *     path="/api/translations/{translation}",
 *     tags={"Translations"},
 *     summary="Delete a translation",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="translation", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=204, description="Deleted translation")
 * )
 *
 * @OA\Get(
 *     path="/api/translations/export/{locale}",
 *     tags={"Export"},
 *     summary="Export flat translations for a locale",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="locale", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Parameter(name="tag", in="query", @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Flat translation payload"),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 */
class Documentation
{
}
