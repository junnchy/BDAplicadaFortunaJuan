<?php

namespace App\Controllers;

class AuthController extends BaseController
{
    public function login()
    {
        // Si ya está logueado, redirigir al dashboard
        if (auth()->loggedIn()) {
            return redirect()->to('/dashboard');
        }
        
        // Si es POST, procesar el login
        if ($this->request->getMethod() === 'POST') {
            $credentials = [
                'email'    => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
            ];
            
            $remember = (bool) $this->request->getPost('remember');
            
            $result = auth()->attempt($credentials, $remember);
            
            if ($result->isOK()) {
                // Login exitoso, redirigir al dashboard
                return redirect()->to('/dashboard')->with('message', 'Bienvenido al Sistema ETL Dashboard');
            }
            
            // Login fallido, regresar al login con error
            return redirect()->back()->with('error', $result->reason());
        }
        
        // Mostrar formulario de login personalizado
        return view('auth/login');
    }
    
    public function logout()
    {
        auth()->logout();
        return redirect()->to('/login')->with('message', 'Has cerrado sesión exitosamente');
    }
    
    public function register()
    {
        // Si ya está logueado, redirigir al dashboard
        if (auth()->loggedIn()) {
            return redirect()->to('/dashboard');
        }
        
        return view('auth/register');
    }
}