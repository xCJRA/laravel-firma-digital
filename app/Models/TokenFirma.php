<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenFirma extends Model
{
    protected $fillable = [
        'firmante_id',
        'token',
        'usado',
        'expira_at',
    ];

    protected $casts = [
        'expira_at' => 'datetime',
        'usado'     => 'boolean',
    ];

    protected $table = "tokens_firma";
    public function firmante()
    {
        return $this->belongsTo(Firmante::class);
    }

    // ¿Este token sigue siendo válido?
    public function esValido(): bool
    {
        return !$this->usado && $this->expira_at->isFuture();
    }
}
