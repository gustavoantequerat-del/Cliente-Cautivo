<?php

namespace Database\Seeders;

use App\Modelos\Modelo_Unidad_Negocio;
use Illuminate\Database\Seeder;

/** Catálogo base. Seguro para producción: php artisan db:seed --class=Sembrador_Unidades_Negocio */
class Sembrador_Unidades_Negocio extends Seeder
{
    public function run(): void
    {
        $Unidades_Negocio = [
            ['BCS', 'Compliance Solutions', 'Monitoreo transaccional con Crystal Expert y cumplimiento normativo en activos virtuales.'],
            ['NEXUM', 'Formación y certificación', 'Programa NEXUM para Servicios Financieros y talleres presenciales por nivel.'],
            ['RESEARCH', 'Research & Market Intelligence', 'Reportes, tableros y análisis del mercado de activos virtuales en Bolivia.'],
            ['LABS', 'Advisory & Strategy', 'Estrategia de adopción institucional, tesorería con activos virtuales y tokenización.'],
            ['BUILD', 'Desarrollo e infraestructura', 'Diseño, desarrollo e implementación de soluciones blockchain a medida.'],
            ['W3M', 'Web3 Marketing & Comunicación', 'Contenidos, educación financiera y comunicación para el ecosistema Web 3.0.'],
        ];
        foreach ($Unidades_Negocio as $Posicion => [$Codigo, $Nombre, $Descripcion]) {
            Modelo_Unidad_Negocio::updateOrCreate(['codigo' => $Codigo], ['nombre' => $Nombre, 'descripcion' => $Descripcion, 'orden' => $Posicion + 1]);
        }
    }
}
