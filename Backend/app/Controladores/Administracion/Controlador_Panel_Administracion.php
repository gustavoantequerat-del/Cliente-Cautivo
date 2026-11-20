<?php

namespace App\Controladores\Administracion;

use App\Modelos\Modelo_Entregable;
use App\Modelos\Modelo_Factura;
use App\Modelos\Modelo_Institucion;
use App\Modelos\Modelo_Solicitud_Horas;
use App\Modelos\Modelo_Usuario;
use App\Servicios\Servicio_Definicion_Cms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class Controlador_Panel_Administracion
{
    public function Mostrar_Resumen(Request $Peticion): Response
    {
        $Usuario = $Peticion->user();
        $Ids_Instituciones = $Usuario->Obtener_Ids_Instituciones_A_Cargo();
        // El staff cuenta todo; el ejecutivo de cuenta, solo sus instituciones
        $De_Sus_Instituciones = fn (Builder $Consulta, string $Columna = 'institucion_id') => $Ids_Instituciones === null
            ? $Consulta : $Consulta->whereIn($Columna, $Ids_Instituciones);

        $Recursos_Por_Grupo = [];
        foreach (Servicio_Definicion_Cms::Obtener_Recursos_Permitidos($Usuario) as $Clave_Recurso => $Recurso) {
            $Recursos_Por_Grupo[$Recurso['Grupo']][] = [
                'Clave' => $Clave_Recurso, 'Titulo' => $Recurso['Titulo'], 'Icono' => $Recurso['Icono'],
                'Cantidad' => Servicio_Definicion_Cms::Consultar_Registros($Recurso, $Usuario)->count(),
            ];
        }

        return Inertia::render('Administracion/Pagina_Resumen', [
            'Indicadores' => [
                ['Etiqueta' => $Usuario->Es_Staff() ? 'Instituciones activas' : 'Mis instituciones', 'Icono' => 'edificio', 'Url' => $Usuario->Es_Staff() ? '/admin/instituciones' : '/admin/usuarios',
                    'Valor' => $De_Sus_Instituciones(Modelo_Institucion::where('esta_activa', true), 'id')->count()],
                ['Etiqueta' => 'Clientes con acceso', 'Icono' => 'usuarios', 'Url' => '/admin/usuarios',
                    'Valor' => $De_Sus_Instituciones(Modelo_Usuario::where('esta_activo', true)->whereIn('rol', [Modelo_Usuario::Rol_Cliente_Basico, Modelo_Usuario::Rol_Cliente_Institucional]))->count()],
                ['Etiqueta' => 'Solicitudes de horas', 'Icono' => 'reloj', 'Url' => '/admin/solicitudes-horas',
                    'Valor' => $De_Sus_Instituciones(Modelo_Solicitud_Horas::whereIn('estado', ['pendiente', 'en_atencion']))->count()],
                ['Etiqueta' => 'Esperando aprobación', 'Icono' => 'boveda', 'Url' => '/admin/entregables',
                    'Valor' => $De_Sus_Instituciones(Modelo_Entregable::where('estado', 'pendiente_aprobacion'))->count()],
                ['Etiqueta' => 'Facturas pendientes', 'Icono' => 'facturacion', 'Url' => '/admin/facturas',
                    'Valor' => $De_Sus_Instituciones(Modelo_Factura::where('estado', 'pendiente'))->count()],
            ],
            'Recursos_Por_Grupo' => $Recursos_Por_Grupo,
        ]);
    }
}
