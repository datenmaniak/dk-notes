<?php

namespace App\View\Composers;

use App\Models\Category;
use Illuminate\View\View;

class SidebarComposer
{
    public function compose(View $view)
    {
        $view->with('categoriasConNotas', Category::withCount('notes')->get());
    }
}
