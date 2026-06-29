<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // Agregar esta propiedad
    protected $fillable = [
        'name',
        'slug',
    ];

    // error sin agregar esta propiedad
    //     Illuminate\Database\Eloquent\MassAssignmentException
    //    Add [name] to fillable property to allow mass assignment on [App\Models\Category].
    // //
    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
