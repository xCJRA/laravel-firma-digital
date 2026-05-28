<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Documento;
use App\Models\Firmante;
use App\Models\TokenFirma;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class DocumentoService
{
    /**
     * Crea el documento y guarda el PDF si viene uno.
     */
    public function crear(array $datos, ?UploadedFile $archivo, string $ip): Documento
    {
        $rutaArchivo = null;

        if ($archivo) {
            // Guarda en storage/app/documentos/originales/
            // El nombre usa UUID para evitar colisiones
            $rutaArchivo = $archivo->storeAs(
                'documentos/originales',
                Str::uuid() . '.pdf'
            );
        }

        $documento = Documento::create([
            'nombre'           => $datos['nombre'],
            'descripcion'      => $datos['descripcion'] ?? null,
            'archivo_original' => $rutaArchivo,
            'estado'           => 'pendiente',
        ]);

        Auditoria::registrar('documento_creado', 'Documento', $documento->id, [
            'nombre' => $documento->nombre,
            'tiene_archivo' => !is_null($rutaArchivo),
        ], $ip);

        return $documento;
    }

    /**
     * Agrega firmantes al documento y genera un token para cada uno.
     */
    public function agregarFirmantes(Documento $documento, array $firmantes, string $ip): void
    {
        // Ordenamos por el campo 'orden' antes de insertar
        usort($firmantes, fn($a, $b) => $a['orden'] <=> $b['orden']);

        foreach ($firmantes as $datosFirmante) {
            $firmante = Firmante::create([
                'documento_id' => $documento->id,
                'nombre'       => $datosFirmante['nombre'],
                'email'        => $datosFirmante['email'],
                'orden'        => $datosFirmante['orden'],
                'estado'       => 'pendiente',
            ]);

            // Genera el token y simula el envío del email
            $this->generarYEnviarToken($firmante);

            Auditoria::registrar('firmante_agregado', 'Firmante', $firmante->id, [
                'documento_id' => $documento->id,
                'nombre'       => $firmante->nombre,
                'email'        => $firmante->email,
                'orden'        => $firmante->orden,
            ], $ip);
        }

        // Si el documento estaba en "pendiente", pasa a "en_proceso"
        if ($documento->estado === 'pendiente') {
            $documento->update(['estado' => 'en_proceso']);
        }
    }

    /**
     * Genera un token único y lo guarda. En producción aquí enviarías el email.
     */
    public function generarYEnviarToken(Firmante $firmante): TokenFirma
    {
        // Invalida tokens anteriores de este firmante (por si se reenvía)
        $firmante->tokens()->update(['usado' => true]);

        $token = TokenFirma::create([
            'firmante_id' => $firmante->id,
            'token'       => Str::random(64),
            'usado'       => false,
            'expira_at'   => now()->addHours(48),
        ]);

        // TODO: aquí iría Mail::to($firmante->email)->send(new LinkFirmaMail($token));
        // Por ahora lo logueamos para poder probarlo en Postman
        \Log::info("Link de firma para {$firmante->nombre}: /api/firmar/{$token->token}");

        return $token;
    }

    /**
     * Reenvía el link de firma a un firmante específico.
     */
    public function reenviarToken(Documento $documento, int $firmanteId, string $ip): TokenFirma
    {
        $firmante = Firmante::where('id', $firmanteId)
                            ->where('documento_id', $documento->id)
                            ->where('estado', 'pendiente')
                            ->firstOrFail();

        $token = $this->generarYEnviarToken($firmante);

        Auditoria::registrar('token_reenviado', 'Firmante', $firmante->id, [
            'documento_id' => $documento->id,
            'email'        => $firmante->email,
        ], $ip);

        return $token;
    }
}
