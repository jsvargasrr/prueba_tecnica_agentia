<?php

namespace App\OpenApi;

/**
 * @OA\Info(
 *   title="AgentIA API",
 *   version="1.0.0",
 *   description="API para subir PDFs, indexar embeddings (pgvector) y consultas semánticas"
 * )
 * @OA\Server(
 *   url=L5_SWAGGER_CONST_HOST,
 *   description="Servidor base"
 * )
 *
 * @OA\Tag(name="Auth", description="Registro y login")
 * @OA\Tag(name="Documents", description="Subida e indexado de PDFs")
 * @OA\Tag(name="Query", description="Búsqueda semántica")
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 *
 * @OA\Parameter(
 *   parameter="AcceptLanguage",
 *   name="Accept-Language",
 *   in="header",
 *   required=false,
 *   description="Idioma preferido (es, en, pt, ...)",
 *   @OA\Schema(type="string", example="es")
 * )
 */
class OpenApi {}
