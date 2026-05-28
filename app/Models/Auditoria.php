<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $fillable = [
        'accion',
        'entidad',
        'entidad_id',
        'datos',
        'ip_address',
    ];

    protected $casts = [
        'datos' => 'array',
    ];

    protected $table = "auditoria";
    // Método estático para registrar fácil desde cualquier lado
    public static function registrar(string $accion, string $entidad, int $id, array $datos = [], ?string $ip = null): void
    {
        self::create([
            'accion'     => $accion,
            'entidad'    => $entidad,
            'entidad_id' => $id,
            'datos'      => $datos,
            'ip_address' => $ip,
        ]);
    }
}
