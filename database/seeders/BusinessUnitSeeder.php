<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use Illuminate\Database\Seeder;

/** Catálogo base. Seguro para producción: php artisan db:seed --class=BusinessUnitSeeder */
class BusinessUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['BCS', 'Compliance Solutions', 'Monitoreo transaccional con Crystal Expert y cumplimiento normativo en activos virtuales.'],
            ['NEXUM', 'Formación y certificación', 'Programa NEXUM para Servicios Financieros y talleres presenciales por nivel.'],
            ['RESEARCH', 'Research & Market Intelligence', 'Reportes, tableros y análisis del mercado de activos virtuales en Bolivia.'],
            ['LABS', 'Advisory & Strategy', 'Estrategia de adopción institucional, tesorería con activos virtuales y tokenización.'],
            ['BUILD', 'Desarrollo e infraestructura', 'Diseño, desarrollo e implementación de soluciones blockchain a medida.'],
            ['W3M', 'Web3 Marketing & Comunicación', 'Contenidos, educación financiera y comunicación para el ecosistema Web 3.0.'],
        ];
        foreach ($units as $i => [$code, $name, $description]) {
            BusinessUnit::updateOrCreate(['code' => $code], ['name' => $name, 'description' => $description, 'sort' => $i + 1]);
        }
    }
}
