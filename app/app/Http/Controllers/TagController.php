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
     * Obtener todas las etiquetas (para el modal)
     */
    public function index()
    {
        $tags = Tag::orderBy('name')->get();

        return response()->json($tags);
    }

    /**
     * Crear una nueva etiqueta
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:30|min:2|unique:tags,name',
        ]);

        $tag = Tag::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'tag' => $tag]);
    }

    /**
     * Eliminar una etiqueta (solo admin)
     */
    public function destroy(Tag $tag)
    {
        if (! Auth::user()->is_admin) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $tag->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Asignar etiquetas a una nota
     */
    public function assign(Request $request, Note $note)
    {
        if ($note->user_id !== Auth::id() && ! Auth::user()->is_admin) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        $note->tags()->sync($request->tags);

        return response()->json(['success' => true]);
    }

    /**
     * Obtener etiquetas de una nota
     */
    public function getNoteTags(Note $note)
    {
        if ($note->user_id !== Auth::id() && ! Auth::user()->is_admin) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json($note->tags);
    }
}
