<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'archivo_original',
        'archivo_firmado',
        'estado',
        'descripcion',
    ];

    // Un documento tiene muchos firmantes
    public function firmantes()
    {
        return $this->hasMany(Firmante::class);
    }

    // Un documento tiene muchas firmas
    public function firmas()
    {
        return $this->hasMany(Firma::class);
    }

    // El siguiente firmante que debe firmar (el de menor orden que sigue pendiente)
    public function siguienteFirmante()
    {
        return $this->firmantes()
                    ->where('estado', 'pendiente')
                    ->orderBy('orden')
                    ->first();
    }

    // ¿Todos firmaron?
    public function todosHanFirmado(): bool
    {
        return $this->firmantes()
                    ->where('estado', 'pendiente')
                    ->count() === 0;
    }
}
