<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles si no existen
        Role::firstOrCreate(['name' => 'administrador']);
        Role::firstOrCreate(['name' => 'cliente']);

        // Asignar el rol de administrador al usuario principal
        $adminEmail = 'info@mdmgdesarrolloweb.com'; // cámbialo si lo deseas
        $user = User::where('email', $adminEmail)->first();

        if ($user) {
            $user->assignRole('administrador');
            $this->command->info("Rol 'administrador' asignado al usuario {$adminEmail}");
        } else {
            $this->command->warn("No se encontró ningún usuario con el email {$adminEmail}");
        }

        $this->command->info('Roles básicos creados correctamente.');
    }
}
