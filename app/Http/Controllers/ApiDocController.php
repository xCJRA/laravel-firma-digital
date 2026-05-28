<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'API de Firma Electrónica de Documentos',
    description: 'API REST para gestión de firma electrónica de documentos. Permite subir documentos, registrar firmantes con orden secuencial, enviar links de firma con expiración y generar el PDF final firmado.',
)]
#[OA\Server(
    url: 'http://localhost:8000/api',
    description: 'Servidor local de desarrollo'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Token Bearer obtenido en POST /login'
)]
class ApiDocController extends Controller
{
    // Este controller solo existe para alojar la configuración global de Swagger.
    // No tiene métodos ni rutas.
}
