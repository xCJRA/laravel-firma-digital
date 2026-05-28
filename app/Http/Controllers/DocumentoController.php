<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentoRequest;
use App\Http\Requests\StoreFirmanteRequest;
use App\Models\Documento;
use App\Services\DocumentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DocumentoController extends Controller
{
    public function __construct(private DocumentoService $service) {}

    #[OA\Get(
        path: '/documentos',
        tags: ['Documentos'],
        summary: 'Listar documentos',
        description: 'Devuelve todos los documentos paginados con sus firmantes.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de documentos',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'nombre', type: 'string', example: 'Contrato de servicios'),
                                    new OA\Property(property: 'estado', type: 'string', example: 'en_proceso'),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2026-05-27T10:00:00'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(property: 'total', type: 'integer', example: 5),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
        ]
    )]
    public function index(): JsonResponse
    {
        $documentos = Documento::with('firmantes')->latest()->paginate(15);
        return response()->json($documentos);
    }

    #[OA\Post(
        path: '/documentos',
        tags: ['Documentos'],
        summary: 'Crear documento',
        description: 'Crea un nuevo documento. El archivo PDF es opcional en este punto.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['nombre'],
                    properties: [
                        new OA\Property(property: 'nombre', type: 'string', example: 'Contrato de servicios'),
                        new OA\Property(property: 'descripcion', type: 'string', example: 'Contrato entre las partes involucradas'),
                        new OA\Property(property: 'archivo', type: 'string', format: 'binary', description: 'Archivo PDF (máx. 10MB)'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Documento creado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Documento creado exitosamente.'),
                        new OA\Property(property: 'documento', type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'nombre', type: 'string', example: 'Contrato de servicios'),
                                new OA\Property(property: 'estado', type: 'string', example: 'pendiente'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 422, description: 'Error de validación'),
        ]
    )]
    public function store(StoreDocumentoRequest $request): JsonResponse
    {
        $documento = $this->service->crear(
            $request->validated(),
            $request->file('archivo'),
            $request->ip()
        );

        return response()->json([
            'mensaje'   => 'Documento creado exitosamente.',
            'documento' => $documento->load('firmantes'),
        ], 201);
    }

    #[OA\Get(
        path: '/documentos/{id}',
        tags: ['Documentos'],
        summary: 'Ver documento',
        description: 'Devuelve el detalle completo del documento con firmantes y firmas.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalle del documento'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 404, description: 'Documento no encontrado'),
        ]
    )]
    public function show(Documento $documento): JsonResponse
    {
        $documento->load(['firmantes.firma', 'firmas', 'firmantes.tokens']);
        return response()->json($documento);
    }

    #[OA\Delete(
        path: '/documentos/{id}',
        tags: ['Documentos'],
        summary: 'Eliminar documento',
        description: 'Soft delete — el documento no se borra físicamente de la base de datos.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Documento eliminado correctamente'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 404, description: 'Documento no encontrado'),
        ]
    )]
    public function destroy(Documento $documento): JsonResponse
    {
        $documento->delete();
        return response()->json(['mensaje' => 'Documento eliminado correctamente.']);
    }

    #[OA\Post(
        path: '/documentos/{id}/firmantes',
        tags: ['Firmantes'],
        summary: 'Agregar firmantes',
        description: 'Agrega uno o varios firmantes al documento. Genera un token de firma para cada uno y simula el envío del link por email.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['firmantes'],
                properties: [
                    new OA\Property(
                        property: 'firmantes',
                        type: 'array',
                        items: new OA\Items(
                            required: ['nombre', 'email', 'orden'],
                            properties: [
                                new OA\Property(property: 'nombre', type: 'string', example: 'Juan Pérez'),
                                new OA\Property(property: 'email', type: 'string', example: 'juan@example.com'),
                                new OA\Property(property: 'orden', type: 'integer', example: 1),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Firmantes agregados. Links de firma enviados.'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 422, description: 'Documento ya completado o error de validación'),
        ]
    )]
    public function agregarFirmantes(StoreFirmanteRequest $request, Documento $documento): JsonResponse
    {
        if (in_array($documento->estado, ['completado', 'rechazado'])) {
            return response()->json([
                'mensaje' => 'No se pueden agregar firmantes a un documento en estado ' . $documento->estado . '.',
            ], 422);
        }

        $this->service->agregarFirmantes(
            $documento,
            $request->validated()['firmantes'],
            $request->ip()
        );

        return response()->json([
            'mensaje'   => 'Firmantes agregados. Los links de firma fueron enviados.',
            'documento' => $documento->fresh()->load('firmantes'),
        ], 201);
    }

    #[OA\Post(
        path: '/documentos/{id}/reenviar/{firmante_id}',
        tags: ['Firmantes'],
        summary: 'Reenviar link de firma',
        description: 'Genera un nuevo token e invalida el anterior. Útil cuando el token expiró.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(name: 'firmante_id', in: 'path', required: true,
                schema: new OA\Schema(type: 'integer', example: 2)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Link reenviado exitosamente'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 404, description: 'Firmante no encontrado o ya firmó'),
        ]
    )]
    public function reenviarToken(Request $request, Documento $documento, int $firmanteId): JsonResponse
    {
        $token = $this->service->reenviarToken($documento, $firmanteId, $request->ip());

        return response()->json([
            'mensaje'   => 'Link de firma reenviado exitosamente.',
            'expira_at' => $token->expira_at,
        ]);
    }

    #[OA\Get(
        path: '/documentos/{id}/auditoria',
        tags: ['Auditoría'],
        summary: 'Ver auditoría del documento',
        description: 'Devuelve el log completo de todas las acciones realizadas sobre el documento.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Log de auditoría'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 404, description: 'Documento no encontrado'),
        ]
    )]
    public function auditoria(Documento $documento): JsonResponse
    {
        $logs = \App\Models\Auditoria::where('entidad', 'Documento')
                    ->where('entidad_id', $documento->id)
                    ->orWhere(function ($q) use ($documento) {
                        $q->whereIn('entidad', ['Firmante', 'Firma'])
                          ->whereIn('entidad_id', $documento->firmantes->pluck('id'));
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json($logs);
    }
}
