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

        // Assign the administrator role to the main admin user (set ADMIN_EMAIL in .env)
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $user = User::where('email', $adminEmail)->first();

        if ($user) {
            $user->assignRole('administrador');
            $this->command->info("Role 'administrador' assigned to user {$adminEmail}");
        } else {
            $this->command->warn("No user found with email {$adminEmail}. Set ADMIN_EMAIL in your .env file.");
        }

        $this->command->info('Roles básicos creados correctamente.');
    }
}
