<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Number extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'issued',
        'originalOperator',
        'originalOperatorRaw',
        'currentOperator',
        'currentOperatorRaw',
        'number',
        'prefix',
        'type',
        'typeDescription',
        'queriesLeft',
        'lastPortability',
        'lastPortabilityWhen',
        'lastPortabilityFrom',
        'lastPortabilityFromRaw',
        'lastPortabilityTo',
        'lastPortabilityToRaw',
    ];

    /**
     * Relación con el modelo `File`.
     * Un número pertenece a un archivo.
     */
    public function files()
    {
        return $this->belongsToMany(File::class);
    }
}
