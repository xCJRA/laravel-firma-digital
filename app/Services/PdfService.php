<?php

namespace App\Services;

use App\Models\Documento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfService
{
    /**
     * Genera el PDF final con la tabla de firmas y lo guarda en storage.
     * Devuelve la ruta donde quedó guardado.
     */
    public function generarPdfFirmado(Documento $documento): string
    {
        // Carga las relaciones que necesita el template
        $documento->load(['firmantes.firma']);

        // Renderiza el template blade con los datos del documento
        $pdf = Pdf::loadView('pdf.documento_firmado', compact('documento'));

        // Orientación vertical, tamaño carta
        $pdf->setPaper('letter', 'portrait');

        // Ruta donde se guardará: storage/app/documentos/firmados/
        $nombreArchivo = 'doc_' . $documento->id . '_' . Str::uuid() . '_signed.pdf';
        $ruta = 'documentos/firmados/' . $nombreArchivo;

        Storage::put($ruta, $pdf->output());

        return $ruta;
    }
}
