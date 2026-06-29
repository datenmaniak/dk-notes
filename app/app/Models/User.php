<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// #[Fillable(['name', 'email', 'password'])]
// #[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Relación: Un usuario tiene muchas notas.
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Relación: Un usuario tiene muchas categorías.
     * ¡ESTA ES LA QUE NECESITAS PARA TU FILTRO!
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Los atributos que deben ocultarse para la serialización (JSON).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
