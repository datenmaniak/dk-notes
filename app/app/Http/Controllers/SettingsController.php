<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        
        $settings = [
            'notas_por_pagina' => UserSetting::getValue($userId, 'notas_por_pagina', '5'),
            'tema' => UserSetting::getValue($userId, 'tema', 'auto'),
            'directorio_notas' => UserSetting::getValue($userId, 'directorio_notas', base_path('notes')),
        ];
        
        return view('settings.index', compact('settings'));
    }
    
    public function update(Request $request)
    {
        $userId = Auth::id();
        
        $request->validate([
            'notas_por_pagina' => 'in:5,10,20',
            'tema' => 'in:light,dark,auto',
            'directorio_notas' => 'nullable|string',
        ]);
        
        if ($request->has('notas_por_pagina')) {
            UserSetting::setValue($userId, 'notas_por_pagina', $request->notas_por_pagina);
        }
        
        if ($request->has('tema')) {
            UserSetting::setValue($userId, 'tema', $request->tema);
        }
        
        if ($request->has('directorio_notas')) {
            UserSetting::setValue($userId, 'directorio_notas', $request->directorio_notas);
        }
        
        return redirect()->route('settings.index')->with('success', 'Configuración actualizada.');
    }
}
