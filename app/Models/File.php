<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = ['name', 'user_id', 'processed', 'downloading', 'downloaded'];

    /**
     * Relación con el modelo `Number`.
     * Un archivo tiene muchos números.
     */
    public function numbers()
    {
        return $this->belongsToMany(Number::class);
    }

    /**
     * Relación con el modelo `User`.
     * Un archivo pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
