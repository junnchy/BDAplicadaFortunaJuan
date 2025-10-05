<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;

/**
 * Controlador de autenticación para la API
 */
class AuthController extends Controller
{
    /**
     * POST /api/public/login
     * Autenticar usuario y obtener token de sesión
     */
    public function login()
    {
        try {
            $rules = [
                'email' => 'required|valid_email',
                'password' => 'required'
            ];

            if (!$this->validate($rules)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors(),
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }

            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            // Intentar autenticación usando Shield
            $credentials = [
                'email' => $email,
                'password' => $password
            ];

            $result = auth()->attempt($credentials);

            if (!$result->isOK()) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }

            // Obtener información del usuario autenticado
            $user = auth()->user();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email
                    ],
                    'session_info' => [
                        'logged_in' => auth()->loggedIn(),
                        'login_time' => date('Y-m-d H:i:s')
                    ]
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en AuthController::login: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Login error: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * POST /api/public/logout
     * Cerrar sesión del usuario
     */
    public function logout()
    {
        try {
            auth()->logout();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Logout successful',
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en AuthController::logout: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Logout error: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
}