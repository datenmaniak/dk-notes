<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{


    protected $fillable = [
    'title',
    'slug',
    'content_markdown',
    'content_html',
    'file_path',
    'checksum',
    'category_id',
    'user_id',
    'created_at',
    'updated_at',
    ];


    // Relación con Category (muchos a uno)
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relación con User (muchos a uno)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con Tag (muchos a muchos)
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
