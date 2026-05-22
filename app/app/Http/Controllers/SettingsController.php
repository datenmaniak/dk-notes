<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use App\Models\Category;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        
        $rutaPersonal = UserSetting::getValue($userId, 'ruta_personal', '');
        $rutaBase = base_path('public/notes');
        $rutaCompleta = $rutaPersonal ? $rutaBase . '/' . $rutaPersonal : $rutaBase;
        
        $settings = [
            'notas_por_pagina' => UserSetting::getValue($userId, 'notas_por_pagina', '5'),
            'tema' => UserSetting::getValue($userId, 'tema', 'auto'),
            'ruta_personal' => $rutaPersonal,
            'ruta_base' => $rutaBase,
            'ruta_completa' => $rutaCompleta,
        ];
        
        return view('settings.index', compact('settings'));
    }
    
    public function update(Request $request)
    {
        $userId = Auth::id();
        
        $request->validate([
            'notas_por_pagina' => 'in:5,10,20',
            'tema' => 'in:light,dark,auto',
            'ruta_personal' => 'nullable|string|max:16',
        ]);
        
        // Procesar ruta personal
        if ($request->has('ruta_personal')) {
            $rutaPersonal = trim($request->ruta_personal);
            
            if ($rutaPersonal === '') {
                // Usuario quiere usar solo el directorio base
                UserSetting::setValue($userId, 'ruta_personal', '');
                UserSetting::setValue($userId, 'directorio_notas', base_path('public/notes'));
                
            } else {
                // Normalizar a minúsculas
                $rutaPersonal = strtolower($rutaPersonal);
                
                // Validación 1: Solo letras
                if (!preg_match('/^[a-z]+$/', $rutaPersonal)) {
                    return redirect()->route('settings.index')->with('error', '❌ La ruta personal solo puede contener letras minúsculas (a-z).');
                }
                
                // Validación 2: Longitud entre 5 y 16
                $longitud = strlen($rutaPersonal);
                if ($longitud < 5 || $longitud > 16) {
                    return redirect()->route('settings.index')->with('error', '❌ La ruta personal debe tener entre 5 y 16 caracteres.');
                }
                
                // Validación 3: Palabras reservadas
                $reservadas = ['admin', 'system', 'public', 'root', 'devops', 'storage', 'images', 'default', 'general'];
                if (in_array($rutaPersonal, $reservadas)) {
                    return redirect()->route('settings.index')->with('error', '❌ El nombre "' . $rutaPersonal . '" no está permitido. Elige otro.');
                }
                
                // Validación 4: Unicidad (no usado por otro usuario)
                $usadoPor = UserSetting::where('key', 'ruta_personal')
                    ->where('value', $rutaPersonal)
                    ->where('user_id', '!=', $userId)
                    ->first();
                
                if ($usadoPor) {
                    $usuario = \App\Models\User::find($usadoPor->user_id);
                    return redirect()->route('settings.index')->with('error', '❌ El nombre "' . $rutaPersonal . '" ya está en uso por el usuario "' . ($usuario->name ?? 'desconocido') . '". Elige otro.');
                }
                
                // Crear directorio si no existe
                $rutaCompleta = base_path('public/notes/' . $rutaPersonal);
                if (!File::exists($rutaCompleta)) {
                    try {
                        File::makeDirectory($rutaCompleta, 0755, true);
                        $mensajeDirectorio = "📁 Directorio creado automáticamente: " . $rutaCompleta;
                    } catch (\Exception $e) {
                        return redirect()->route('settings.index')->with('error', '❌ No se pudo crear el directorio. Verifica los permisos del contenedor.');
                    }
                } else {
                    $mensajeDirectorio = null;
                }
                
                // Guardar ruta personal
                UserSetting::setValue($userId, 'ruta_personal', $rutaPersonal);
                UserSetting::setValue($userId, 'directorio_notas', $rutaCompleta);
                
                $mensajeExito = '✅ Ruta personal guardada correctamente.';
                if ($mensajeDirectorio) {
                    $mensajeExito .= ' ' . $mensajeDirectorio;
                }
                
                // Actualizar otras configuraciones
                if ($request->has('notas_por_pagina')) {
                    UserSetting::setValue($userId, 'notas_por_pagina', $request->notas_por_pagina);
                }
                
                if ($request->has('tema')) {
                    UserSetting::setValue($userId, 'tema', $request->tema);
                }
                
                return redirect()->route('settings.index')->with('success', $mensajeExito);
            }
        }
        
        // Actualizar otras configuraciones (sin cambios en ruta personal)
        if ($request->has('notas_por_pagina')) {
            UserSetting::setValue($userId, 'notas_por_pagina', $request->notas_por_pagina);
        }
        
        if ($request->has('tema')) {
            UserSetting::setValue($userId, 'tema', $request->tema);
        }
        
        return redirect()->route('settings.index')->with('success', '✅ Configuración actualizada.');
    }
}