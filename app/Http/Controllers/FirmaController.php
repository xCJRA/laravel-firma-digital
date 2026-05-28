<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Firma;
use App\Models\TokenFirma;
use App\Services\PdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class FirmaController extends Controller
{
    public function __construct(private PdfService $pdfService) {}

    #[OA\Post(
        path: '/firmar/{token}',
        tags: ['Firma'],
        summary: 'Registrar firma',
        description: 'Endpoint público. El firmante valida su token y registra su firma con timestamp e IP. Si es el último firmante, genera el PDF final.',
        parameters: [
            new OA\Parameter(
                name: 'token',
                in: 'path',
                required: true,
                description: 'Token único recibido por email',
                schema: new OA\Schema(type: 'string', example: 'a3f9c2e1b8d74506a2e1c3f9b8d74506a3f9c2e1b8d74506a2e1c3f9b8d74506')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Firma registrada. Si todos firmaron, incluye la ruta del PDF final.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Firma registrada exitosamente.'),
                        new OA\Property(property: 'firmante', type: 'string', example: 'Juan Pérez'),
                        new OA\Property(property: 'firmado_at', type: 'string', example: '2026-05-27T14:35:00'),
                        new OA\Property(property: 'siguiente', type: 'string', nullable: true,
                            example: 'El documento está esperando la firma de: María García'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Token inválido, expirado o no es el turno del firmante'),
        ]
    )]
    public function firmar(Request $request, string $token): JsonResponse
    {
        $tokenModel = TokenFirma::where('token', $token)
                                ->with('firmante.documento')
                                ->first();

        if (!$tokenModel || !$tokenModel->esValido()) {
            return response()->json([
                'mensaje' => 'El link de firma no es válido o ha expirado.',
            ], 422);
        }

        $firmante  = $tokenModel->firmante;
        $documento = $firmante->documento;

        if (in_array($documento->estado, ['completado', 'rechazado'])) {
            return response()->json([
                'mensaje' => 'Este documento ya no acepta firmas.',
            ], 422);
        }

        $siguienteFirmante = $documento->siguienteFirmante();

        if (!$siguienteFirmante || $siguienteFirmante->id !== $firmante->id) {
            return response()->json([
                'mensaje' => 'Aún no es tu turno para firmar. Debes esperar a que firme el firmante anterior.',
            ], 422);
        }

        $firma = Firma::create([
            'firmante_id'  => $firmante->id,
            'documento_id' => $documento->id,
            'ip_address'   => $request->ip(),
            'firmado_at'   => now(),
        ]);

        $tokenModel->update(['usado' => true]);
        $firmante->update(['estado' => 'firmado']);

        Auditoria::registrar('firma_registrada', 'Firma', $firma->id, [
            'documento_id' => $documento->id,
            'firmante_id'  => $firmante->id,
            'firmante'     => $firmante->nombre,
            'ip'           => $request->ip(),
        ], $request->ip());

        $documento->refresh();

        if ($documento->todosHanFirmado()) {
            return $this->completarDocumento($documento, $firmante->nombre);
        }

        $siguienteNombre = $documento->siguienteFirmante()?->nombre;

        return response()->json([
            'mensaje'    => 'Firma registrada exitosamente.',
            'firmante'   => $firmante->nombre,
            'firmado_at' => $firma->firmado_at,
            'siguiente'  => $siguienteNombre
                ? "El documento está esperando la firma de: {$siguienteNombre}"
                : null,
        ]);
    }

    private function completarDocumento($documento, string $ultimoFirmante): JsonResponse
    {
        $rutaPdf = $this->pdfService->generarPdfFirmado($documento);

        $documento->update([
            'estado'          => 'completado',
            'archivo_firmado' => $rutaPdf,
        ]);

        Auditoria::registrar('documento_completado', 'Documento', $documento->id, [
            'archivo_firmado' => $rutaPdf,
            'total_firmantes' => $documento->firmantes->count(),
        ]);

        return response()->json([
            'mensaje'         => 'Documento firmado exitosamente por todos los firmantes.',
            'firmante'        => $ultimoFirmante,
            'firmado_at'      => now(),
            'documento'       => [
                'id'              => $documento->id,
                'nombre'          => $documento->nombre,
                'estado'          => 'completado',
                'archivo_firmado' => $rutaPdf,
            ],
        ]);
    }
}
