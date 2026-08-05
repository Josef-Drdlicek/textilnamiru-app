<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Rozcestník po přihlášení — admin míří na přehled zakázek,
 * designér na založení/pokračování zakázky.
 */
class HomeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return $request->user()->isAdmin()
            ? redirect()->route('admin.orders.index')
            : redirect()->route('orders.welcome');
    }
}
