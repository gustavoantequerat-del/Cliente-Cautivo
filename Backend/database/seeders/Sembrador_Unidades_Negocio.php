<?php

namespace Database\Seeders;

use App\Modelos\Modelo_Unidad_Negocio;
use Illuminate\Database\Seeder;

/**
 * Catálogo inicial del Ecosistema Blockfinity. Seguro para producción y repetible:
 *   php artisan db:seed --class=Sembrador_Unidades_Negocio --force
 * Solo crea lo que falta y completa subtítulo y descripción vacíos: no pisa lo que ya se editó en el CMS.
 */
class Sembrador_Unidades_Negocio extends Seeder
{
    public function run(): void
    {
        $Productos = [
            ['BCS', 'Compliance Solutions', 'Monitoreo de activos virtuales y cumplimiento normativo',
                'Monitoreo transaccional con Crystal Expert, evaluación de riesgo de contrapartes y acompañamiento para cumplir la normativa sobre activos virtuales.'],
            ['NEXUM', 'Formación y certificación', 'Programa NEXUM para Servicios Financieros',
                'Formación por niveles para directorio, mandos medios y equipos operativos, con talleres presenciales y certificación verificable.'],
            ['RESEARCH', 'Research & Market Intelligence', 'Datos y análisis del mercado de activos virtuales en Bolivia',
                'Reportes periódicos, tableros y análisis a medida sobre el mercado de activos virtuales, stablecoins y regulación.'],
            ['LABS', 'Advisory & Strategy', 'Estrategia de adopción institucional',
                'Acompañamiento estratégico para la adopción institucional de activos virtuales, tesorería y tokenización.'],
            ['BUILD', 'Desarrollo e infraestructura', 'Soluciones blockchain a medida',
                'Diseño, desarrollo e implementación de soluciones blockchain para la operación de su institución.'],
            ['W3M', 'Web3 Marketing & Comunicación', 'Contenidos y educación financiera Web 3.0',
                'Estrategia de contenidos, educación financiera y comunicación para el ecosistema Web 3.0.'],
        ];
        foreach ($Productos as $Posicion => [$Codigo, $Titulo, $Subtitulo, $Descripcion]) {
            $Producto = Modelo_Unidad_Negocio::firstOrNew(['codigo' => $Codigo], ['nombre' => $Titulo, 'orden' => $Posicion + 1]);
            $Producto->subtitulo ??= $Subtitulo;
            $Producto->descripcion ??= $Descripcion;
            $Producto->save();
        }
    }
}
