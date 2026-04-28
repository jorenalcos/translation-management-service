<?php

namespace App\OpenApi;

/**
 * @OA\Info(
 *     title="Translation Service API",
 *     version="1.0.0",
 *     description="API for authenticated translation CRUD, search, tagging, and locale export."
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
 *     required={"key", "locale", "value"},
 *     @OA\Property(property="key", type="string", example="auth.login.title"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Login page title"),
 *     @OA\Property(property="locale", type="string", example="en"),
 *     @OA\Property(property="locale_name", type="string", example="English"),
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
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="translation", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TranslationPayload")),
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
