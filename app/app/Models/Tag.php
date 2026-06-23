<?php

namespace App\Models;

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

    public function notes()
    {
        return $this->belongsToMany(Note::class);
    }

}
