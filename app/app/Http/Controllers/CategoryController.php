<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CategoryController extends Controller
{



    public function recalculate(Request $request)
    {
        $userId = Auth::id(); // <-- ID del usuario autenticado
        $directorioNotas = $this->getDirectorioNotas();

        if (! is_dir($directorioNotas)) {
            return redirect()->route('notes.index')->with('error', 'Directorio de notas no encontrado.');
        }

        // 1. Obtener categorías existentes SOLO del usuario autenticado
        $categoriasBD = Category::where('user_id', $userId)->get();

        // 2. Escanear directorios (primer nivel)
        $directorios = $this->getDirectoriosNivel1($directorioNotas);

        $nuevas = 0;
        $reutilizadas = 0;
        $huérfanas = 0;
        $actualizadas = 0;

        // 3. Crear/actualizar categorías basadas en directorios
        foreach ($directorios as $nombreDirectorio) {
            $slug = Str::slug($nombreDirectorio);

            // Buscar el slug mapeado únicamente para ESTE usuario
            $categoria = Category::where('slug', $slug)
                ->where('user_id', $userId)
                ->first();

            if (! $categoria) {
                // Nueva categoría asociada al usuario
                Category::create([
                    'name' => $nombreDirectorio,
                    'slug' => $slug,
                    'user_id' => $userId, // <-- Ajuste crítico: Asignación del usuario
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
        $slugsDirectorio = array_map(function ($dir) {
            return Str::slug($dir);
        }, $directorios);

        $categoriasHuérfanas = $categoriasBD->filter(function ($cat) use ($slugsDirectorio) {
            return ! in_array($cat->slug, $slugsDirectorio);
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

    private function getDirectorioNotas()
    {
        $userId = Auth::id();

        if (! $userId) {
            return base_path('storage/app/public/notes');
        }

        $rutaPersonal = UserSetting::getValue($userId, 'ruta_personal', '');

        if ($rutaPersonal) {
            return base_path('storage/app/public/notes').'/'.$rutaPersonal;
        }

        return base_path('storage/app/public/notes');
    }

    private function getDirectoriosNivel1(string $basePath): array
    {
        $directorios = [];

        if (! is_dir($basePath)) {
            return [];
        }

        $items = scandir($basePath);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $rutaCompleta = $basePath.DIRECTORY_SEPARATOR.$item;

            if (is_dir($rutaCompleta)) {
                $directorios[] = $item;
            }
        }

        return $directorios;
    }

    public function destroy(Category $category)
    {
        // Ajuste de Seguridad: Verificar que la categoría le pertenezca al usuario autenticado
        if ($category->user_id !== Auth::id()) {
            // abort(403, 'No autorizado para eliminar esta categoría.');
            return redirect()->route('notes.index')->with('error', 'No se puede eliminar una categoría genérica o que no te pertenece.');
        }

        // Verificar que no tenga notas
        if ($category->notes()->count() > 0) {
            return redirect()->route('notes.index')->with('error', 'No se puede eliminar una categoría con notas asociadas.');
        }

        $category->delete();

        return redirect()->route('notes.index')->with('success', 'Categoría eliminada.');
    }
}
