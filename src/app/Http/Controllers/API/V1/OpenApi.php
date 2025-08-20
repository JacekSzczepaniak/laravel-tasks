<?php

namespace App\Http\Controllers\API\V1;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   title="Tasks API",
 *   version="1.0.0",
 *   description="API do zarządzania zadaniami (owner + obserwatorzy)"
 * )
 *
 * @OA\Server(url="/api", description="Local server")
 *
 * @OA\SecurityScheme(
 *   securityScheme="sanctum",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT",
 *   description="Wprowadź Bearer {token}"
 * )
 *
 * @OA\Tag(
 *   name="Tasks",
 *   description="Operacje na zadaniach"
 * )
 */

/**
 * @OA\Schema(
 *   schema="Task",
 *   type="object",
 *   required={"id","owner_id","title","status"},
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="owner_id", type="integer", example=5),
 *   @OA\Property(property="title", type="string", example="Nowe zadanie"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="status", type="string", enum={"todo","in_progress","done"}),
 *   @OA\Property(property="due_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="TaskList",
 *   type="object",
 *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Task")),
 *   @OA\Property(property="meta", type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="per_page", type="integer", example=15),
 *     @OA\Property(property="total", type="integer", example=42)
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="Error",
 *   type="object",
 *   @OA\Property(property="message", type="string"),
 *   @OA\Property(property="errors", type="object")
 * )
 */
class OpenApi
{
}
