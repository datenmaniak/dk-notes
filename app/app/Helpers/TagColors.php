<?php

namespace App\Helpers;

class TagColors
{
    public static function getMap()
    {
        // 🎯 Claves idénticas a los nombres de tu TagSeeder
        return [
            'idea' => [
                'bg' => 'bg-tag-idea/10',
                'text' => 'text-tag-idea',
                'border' => 'border-tag-idea/20',
            ],
            'verificado' => [ // 👈 Cambiado de 'Aplicable' a 'aplicable'
                'bg' => 'bg-tag-aplicable/10',
                'text' => 'text-tag-aplicable',
                'border' => 'border-tag-aplicable/20',
            ],
            'porhacer' => [ // 👈 Cambiado de 'Pendiente' a 'pendiente'
                'bg' => 'bg-tag-pendiente/10',
                'text' => 'text-tag-pendiente',
                'border' => 'border-tag-pendiente/20',
            ],
            'enprueba' => [
                'bg' => 'bg-tag-urgente/10',
                'text' => 'text-tag-urgente',
                'border' => 'border-tag-urgente/20',
            ],
            'readlater' => [ // 👈 Cambiado de 'Read later' a 'readlater'
                'bg' => 'bg-tag-readlater/10',
                'text' => 'text-tag-readlater',
                'border' => 'border-tag-readlater/20',
            ],
            'probar' => [ // 👈 Cambiado de 'Probar' a 'probar'
                'bg' => 'bg-tag-probar/10',
                'text' => 'text-tag-probar',
                'border' => 'border-tag-probar/20',
            ],
            'corregir' => [ // 👈 '
                'bg' => 'bg-tag-probar/10',
                'text' => 'text-tag-probar',
                'border' => 'border-tag-probar/20',
            ],
            'nolabels' => [
                'bg' => 'bg-slate-100 dark:bg-slate-800',
                'text' => 'text-slate-600 dark:text-slate-300',
                'border' => 'border-slate-200/60 dark:border-slate-700/60',
            ],
        ];
    }

    public static function getDefault()
    {
        return [
            'bg' => 'bg-brand-accent/10',
            'text' => 'text-brand-accent',
            'border' => 'border-brand-accent/20',
        ];
    }

    public static function getForTag($tagName)
    {
        // Una pequeña capa de seguridad por si acaso en el frontend
        // usas mayúsculas al mostrar la etiqueta (ej: "Urgente")
        $normalized = strtolower(trim($tagName));

        $map = self::getMap();

        return $map[$normalized] ?? self::getDefault();
    }
}
