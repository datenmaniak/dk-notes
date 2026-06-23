<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tag;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultTags = [
            'idea',
            'aplicable',
            'pendiente',
            'urgente',
            'readlater',
            'probar',
            'testing'
        ];

        foreach ($defaultTags as $tagName) {
            Tag::firstOrCreate([
                'name' => $tagName,
                'user_id' => null, // Esto las define como nativas globales
            ], [
                'slug' => Str::slug($tagName),
            ]);
        }
    }
}
