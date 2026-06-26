<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * Obtener todas las etiquetas accesibles (para el modal)
     */
    public function index()
    {
        // Trae las etiquetas nativas (user_id NULL) MÁS las creadas por el usuario autenticado
        $tags = Tag::whereNull('user_id')
            ->orWhere('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return response()->json($tags);
    }

    /**
     * Crear una nueva etiqueta personalizada
     */
    public function store(Request $request)
    {
        // Validamos que el nombre sea único, pero solo entre las etiquetas que este usuario puede ver
        // (evita que duplique sus propias etiquetas o las nativas)
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:30',
                'min:2',
                function ($attribute, $value, $fail) {
                    $slug = Str::slug($value);
                    $exists = Tag::where(function ($query) {
                        $query->whereNull('user_id')
                            ->orWhere('user_id', Auth::id());
                    })->where('slug', $slug)->exists();

                    if ($exists) {
                        $fail('Ya tienes una etiqueta con este nombre o es una etiqueta del sistema.');
                    }
                },
            ],
        ]);

        $tag = Tag::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'user_id' => Auth::id(), // Se asigna automáticamente al usuario activo
        ]);

        return response()->json(['success' => true, 'tag' => $tag]);
    }

    /**
     * Eliminar una etiqueta personalizada de forma segura
     */
    public function destroy(Tag $tag)
    {
        // 1. Prohibir la eliminación de etiquetas nativas globales (user_id es null)
        if (is_null($tag->user_id)) {
            return response()->json(['error' => 'Las etiquetas nativas del sistema no pueden ser eliminadas.'], 403);
        }

        // 2. Verificar si el usuario autenticado es el dueño real de la etiqueta
        if ($tag->user_id !== Auth::id()) {
            return response()->json(['error' => 'No estás autorizado para eliminar esta etiqueta.'], 403);
        }

        // 3. Eliminación segura
        $tag->delete();

        return response()->json(['success' => true, 'message' => 'Etiqueta eliminada correctamente.']);
    }

    /**
     * Asignar etiquetas a una nota
     */
    public function assign(Request $request, Note $note)
    {
        // Seguridad: El usuario debe ser dueño de la nota
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        // Opcional: Validar que el usuario no inyecte IDs de etiquetas de otros usuarios
        $allowedTagIds = Tag::whereNull('user_id')
            ->orWhere('user_id', Auth::id())
            ->pluck('id')
            ->toArray();

        foreach ($request->tags as $tagId) {
            if (! in_array($tagId, $allowedTagIds)) {
                return response()->json(['error' => 'Una o más etiquetas no son válidas.'], 422);
            }
        }

        $note->tags()->sync($request->tags);

        return response()->json(['success' => true]);
    }

    /**
     * Obtener etiquetas de una nota
     */
    public function getNoteTags(Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json($note->tags);
    }
}
