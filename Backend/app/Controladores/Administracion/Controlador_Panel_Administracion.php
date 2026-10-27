<?php

namespace App\Controladores\Administracion;

use App\Modelos\Modelo_Entregable;
use App\Modelos\Modelo_Factura;
use App\Modelos\Modelo_Institucion;
use App\Modelos\Modelo_Solicitud_Horas;
use App\Modelos\Modelo_Usuario;
use App\Servicios\Servicio_Definicion_Cms;
use Inertia\Inertia;
use Inertia\Response;

class Controlador_Panel_Administracion
{
    public function Mostrar_Resumen(): Response
    {
        $Recursos_Por_Grupo = [];
        foreach (Servicio_Definicion_Cms::Obtener_Todos_Los_Recursos() as $Clave_Recurso => $Recurso) {
            $Recursos_Por_Grupo[$Recurso['Grupo']][] = [
                'Clave' => $Clave_Recurso, 'Titulo' => $Recurso['Titulo'], 'Cantidad' => $Recurso['Modelo']::count(),
            ];
        }

        return Inertia::render('Administracion/Pagina_Resumen', [
            'Indicadores' => [
                ['Etiqueta' => 'Instituciones activas', 'Valor' => Modelo_Institucion::where('esta_activa', true)->count()],
                ['Etiqueta' => 'Credenciales activas', 'Valor' => Modelo_Usuario::where('esta_activo', true)->where('rol', '!=', Modelo_Usuario::Rol_Staff)->count()],
                ['Etiqueta' => 'Solicitudes de horas pendientes', 'Valor' => Modelo_Solicitud_Horas::whereIn('estado', ['pendiente', 'en_atencion'])->count()],
                ['Etiqueta' => 'Entregables esperando aprobación', 'Valor' => Modelo_Entregable::where('estado', 'pendiente_aprobacion')->count()],
                ['Etiqueta' => 'Facturas pendientes', 'Valor' => Modelo_Factura::where('estado', 'pendiente')->count()],
            ],
            'Recursos_Por_Grupo' => $Recursos_Por_Grupo,
        ]);
    }
}
