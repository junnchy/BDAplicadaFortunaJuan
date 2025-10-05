<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateAdmin extends BaseCommand
{
    protected $group       = 'ETL';
    protected $name        = 'etl:create-admin';
    protected $description = 'Crea un usuario administrador para el sistema ETL';

    public function run(array $params)
    {
        $users = auth()->getProvider();

        // Verificar si ya existe un admin
        $existingAdmin = $users->findByCredentials(['email' => 'admin@example.com']);
        
        if ($existingAdmin) {
            CLI::write('El usuario admin ya existe.', 'yellow');
            CLI::write('Email: admin@example.com', 'cyan');
            CLI::write('Password: secret', 'cyan');
            return;
        }

        CLI::write('Creando usuario administrador...', 'green');

        try {
            // Crear usuario
            $user = new \CodeIgniter\Shield\Entities\User([
                'email'    => 'admin@example.com',
                'username' => 'admin',
                'password' => 'secret',
            ]);

            $users->save($user);

            CLI::write('Usuario administrador creado exitosamente!', 'green');
            CLI::write('Email: admin@example.com', 'cyan');
            CLI::write('Password: secret', 'cyan');
            CLI::write('Ahora puedes iniciar sesión en: http://localhost:8080/', 'yellow');

        } catch (\Exception $e) {
            CLI::error('Error al crear usuario: ' . $e->getMessage());
        }
    }
}