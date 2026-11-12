<?php

namespace Database\Seeders;

use App\Models\Counter;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@turnos.test'],
            ['name' => 'Administración', 'password' => 'password', 'role' => 'admin'],
        );

        User::updateOrCreate(
            ['email' => 'agente@turnos.test'],
            ['name' => 'Agente de atención', 'password' => 'password', 'role' => 'agente'],
        );

        $services = [
            ['prefix' => 'A', 'name' => 'Atención general', 'description' => 'Consultas, orientación y trámites en general.', 'average_minutes' => 6],
            ['prefix' => 'B', 'name' => 'Caja y pagos', 'description' => 'Pago de tasas, derechos y contribuciones.', 'average_minutes' => 4],
            ['prefix' => 'C', 'name' => 'Licencias y permisos', 'description' => 'Emisión y renovación de licencias de funcionamiento.', 'average_minutes' => 12],
            ['prefix' => 'D', 'name' => 'Asesoría ciudadana', 'description' => 'Atención personalizada de casos especiales.', 'average_minutes' => 15],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['prefix' => $service['prefix']], $service);
        }

        foreach (range(1, 3) as $number) {
            Counter::updateOrCreate(
                ['name' => 'Ventanilla '.$number],
                ['is_active' => true],
            );
        }
    }
}
