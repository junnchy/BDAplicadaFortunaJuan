<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
    public function index()
    {
        // Verificar si el usuario está logueado
        if (auth()->loggedIn()) {
            // Si está logueado, redirigir al dashboard
            return redirect()->to('/dashboard');
        }
        
        // Si no está logueado, redirigir al login
        return redirect()->to('/login');
    }
    
    /**
     * Página de bienvenida original (para testing o acceso directo)
     */
    public function welcome(): string
    {
        return view('welcome_message');
    }
}
