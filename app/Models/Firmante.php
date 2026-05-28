<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Firmante extends Model
{
    protected $fillable = [
        'documento_id',
        'nombre',
        'email',
        'orden',
        'estado',
    ];

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    // Un firmante tiene muchos tokens (puede que se reenvíe el link)
    public function tokens()
    {
        return $this->hasMany(TokenFirma::class);
    }

    public function firma()
    {
        return $this->hasOne(Firma::class);
    }

    // El token activo (el más reciente, no expirado, no usado)
    public function tokenActivo()
    {
        return $this->tokens()
                    ->where('usado', false)
                    ->where('expira_at', '>', now())
                    ->latest()
                    ->first();
    }
}
