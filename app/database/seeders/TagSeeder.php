<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
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
            'porhacer',
            'enprueba',
            'verificado',
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
