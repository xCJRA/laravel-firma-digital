<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Firma extends Model
{
    protected $fillable = [
        'firmante_id',
        'documento_id',
        'ip_address',
        'firmado_at',
    ];

    protected $casts = [
        'firmado_at' => 'datetime',
    ];

    public function firmante()
    {
        return $this->belongsTo(Firmante::class);
    }

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }
}
