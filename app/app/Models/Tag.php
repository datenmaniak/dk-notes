<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'user_id', // Añadido para asignación masiva
    ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     */
    protected $casts = [
        'user_id' => 'integer',
    ];

    /**
     * Accesor para el atributo 'name'.
     * * Transforma dinámicamente cómo se lee el nombre de la etiqueta en la vista.
     * En la Base de Datos se mantiene 'readlater', pero el usuario verá 'Read Later'.
     * El resto de etiquetas se mostrarán con la primera letra en mayúscula automáticamente.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: function (string $value) {
                if ($value === 'readlater') {
                    return 'Read Later';
                }
                if ($value === 'enprueba') {
                    return 'En prueba';
                }
                if ($value === 'porhacer') {
                    return 'Por hacer';
                }

                // Si la etiqueta es 'nolabels', podemos darle un formato limpio también
                if ($value === 'nolabels') {
                    return 'Sin Etiquetas';
                }

                return ucfirst($value);
            }
        );
    }

    public function notes()
    {
        return $this->belongsToMany(Note::class);
    }
}
