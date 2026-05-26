<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\UserSetting;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function recalculate(Request $request)
    {
        $directorioNotas = $this->getDirectorioNotas();
        
        if (!is_dir($directorioNotas)) {
            return redirect()->route('notes.index')->with('error', 'Directorio de notas no encontrado.');
        }
        
        // 1. Obtener categorías existentes en BD
        $categoriasBD = Category::all();
        
        // 2. Escanear directorios (primer nivel)
        $directorios = $this->getDirectoriosNivel1($directorioNotas);
        
        $nuevas = 0;
        $reutilizadas = 0;
        $huérfanas = 0;
        $actualizadas = 0;
        
        // 3. Crear/actualizar categorías basadas en directorios
        foreach ($directorios as $nombreDirectorio) {
            $slug = Str::slug($nombreDirectorio);
            $categoria = Category::where('slug', $slug)->first();
            
            if (!$categoria) {
                // Nueva categoría
                Category::create([
                    'name' => $nombreDirectorio,
                    'slug' => $slug
                ]);
                $nuevas++;
            } else {
                // Actualizar nombre si es diferente
                if ($categoria->name !== $nombreDirectorio) {
                    $categoria->name = $nombreDirectorio;
                    $categoria->save();
                    $actualizadas++;
                }
                $reutilizadas++;
            }
        }
        
        // 4. Detectar categorías huérfanas (sin directorio)
        $slugsDirectorio = array_map(function($dir) {
            return Str::slug($dir);
        }, $directorios);
        
        $categoriasHuérfanas = $categoriasBD->filter(function($cat) use ($slugsDirectorio) {
            return !in_array($cat->slug, $slugsDirectorio);
        });
        
        $huérfanas = $categoriasHuérfanas->count();
        
        // 5. Para categorías huérfanas, mostrar mensaje (no eliminar automáticamente)
        if ($huérfanas > 0) {
            $nombres = $categoriasHuérfanas->pluck('name')->implode(', ');
            session()->flash('warning', "Categorías huérfanas detectadas (sin directorio): $nombres. Puedes eliminarlas manualmente.");
        }
        
        // 6. Mensaje resumen
        $message = "Categorías recalculadas: $nuevas nuevas, $actualizadas actualizadas, $reutilizadas existentes.";
        if ($huérfanas > 0) {
            $message .= " $huérfanas huérfanas detectadas.";
        }
        
        return redirect()->route('notes.index')->with('success', $message);
    }
    
    // private function getDirectorioNotas()
    // {
    //     // Misma lógica que en ImportNotesCommand
    //     $directorio = 'notes';
        
    //     if (str_starts_with($directorio, '~/')) {
    //         $home = getenv('HOME') ?: $_SERVER['HOME'] ?? '';
    //         $directorio = $home . substr($directorio, 1);
    //     }
        
    //     return base_path('notes');
    // }
    private function getDirectorioNotas()
    {
        // $userId = auth()->id();
        $userId = Auth::id();
        
        if (!$userId) {
            return base_path('storage/app/public/notes');
        }
        
        $rutaPersonal = UserSetting::getValue($userId, 'ruta_personal', '');
        
        
        if ($rutaPersonal) {
            return base_path('storage/app/public/notes') . '/' . $rutaPersonal;

        }
        
        return base_path('storage/app/public/notes');
    }
    
    private function getDirectoriosNivel1(string $basePath): array
    {
        $directorios = [];
        
        if (!is_dir($basePath)) {
            return [];
        }
        
        $items = scandir($basePath);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $rutaCompleta = $basePath . DIRECTORY_SEPARATOR . $item;
            
            if (is_dir($rutaCompleta)) {
                $directorios[] = $item;
            }
        }
        
        return $directorios;
    }
    
    public function destroy(Category $category)
    {
        // Verificar que no tenga notas
        if ($category->notes()->count() > 0) {
            return redirect()->route('notes.index')->with('error', 'No se puede eliminar una categoría con notas asociadas.');
        }
        
        $category->delete();
        return redirect()->route('notes.index')->with('success', 'Categoría eliminada.');
    }
}
