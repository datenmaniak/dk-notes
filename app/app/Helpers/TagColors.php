<?php

namespace App\Helpers;

class TagColors
{
    public static function getMap()
    {
        return [
            'Idea' => [
                'bg' => 'bg-tag-idea/10',
                'text' => 'text-tag-idea',
                'border' => 'border-tag-idea/20',
            ],
            'Aplicable' => [
                'bg' => 'bg-tag-aplicable/10',
                'text' => 'text-tag-aplicable',
                'border' => 'border-tag-aplicable/20',
            ],
            'Pendiente' => [
                'bg' => 'bg-tag-pendiente/10',
                'text' => 'text-tag-pendiente',
                'border' => 'border-tag-pendiente/20',
            ],
            'Urgente' => [
                'bg' => 'bg-tag-urgente/10',
                'text' => 'text-tag-urgente',
                'border' => 'border-tag-urgente/20',
            ],
            'Read later' => [
                'bg' => 'bg-tag-readlater/10',
                'text' => 'text-tag-readlater',
                'border' => 'border-tag-readlater/20',
            ],
            'Probar' => [
                'bg' => 'bg-tag-probar/10',
                'text' => 'text-tag-probar',
                'border' => 'border-tag-probar/20',
            ],
            'Testing' => [
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
            'bg' => 'bg-brand-glow/10',
            'text' => 'text-brand-glow',
            'border' => 'border-brand-glow/20',
        ];
    }

    public static function getForTag($tagName)
    {
        $map = self::getMap();
        return $map[$tagName] ?? self::getDefault();
    }
}