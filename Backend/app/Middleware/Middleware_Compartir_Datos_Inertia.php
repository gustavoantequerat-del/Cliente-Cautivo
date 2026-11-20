<?php

namespace App\Middleware;

use App\Modelos\Modelo_Usuario;
use Illuminate\Http\Request;
use Inertia\Middleware;

/** Datos que reciben todas las páginas del Frontend (usuario, institución, ejecutiva y mensajes) */
class Middleware_Compartir_Datos_Inertia extends Middleware
{
    protected $rootView = 'Vista_Principal';

    public function share(Request $Peticion): array
    {
        $Usuario = $Peticion->user();
        $Institucion = $Usuario?->Institucion?->loadMissing(['Unidades_Contratadas', 'Ejecutivo_Cuenta']);
        $Ejecutivo = $Institucion?->Ejecutivo_Cuenta;

        return array_merge(parent::share($Peticion), [
            'Usuario_Actual' => $Usuario ? [
                'Nombre' => $Usuario->nombre,
                'Email' => $Usuario->email,
                'Iniciales' => $Usuario->Obtener_Iniciales(),
                'Area' => $Usuario->area,
                'Nombre_Rol' => Modelo_Usuario::Roles[$Usuario->rol] ?? $Usuario->rol,
                'Es_Staff' => $Usuario->Es_Staff(),
                'Es_Cliente_Institucional' => $Usuario->Es_Cliente_Institucional(),
                'Numero_Credencial' => $Usuario->numero_credencial,
                // Los clientes muestran su avatar en la barra superior
                'Objetos_Avatar' => $Usuario->Es_Personal_Blockfinity() ? [] : $Usuario->Obtener_Objetos_Equipados(),
            ] : null,
            'Institucion_Actual' => $Institucion ? [
                'Nombre' => $Institucion->nombre,
                'Codigos_Unidades' => $Institucion->Unidades_Contratadas->pluck('codigo')->values(),
            ] : null,
            'Ejecutivo_Cuenta' => $Ejecutivo ? [
                'Nombre' => $Ejecutivo->nombre,
                'Iniciales' => $Ejecutivo->Obtener_Iniciales(),
                'Cargo' => $Ejecutivo->cargo,
                'Email' => $Ejecutivo->email,
                'Enlace_Agenda' => $Ejecutivo->enlace_agenda,
            ] : null,
            'Mensajes_Flash' => [
                'Exito' => fn () => $Peticion->session()->get('Mensaje_Exito'),
                'Error' => fn () => $Peticion->session()->get('Mensaje_Error'),
                'Resultado_Verificacion' => fn () => $Peticion->session()->get('Resultado_Verificacion'),
            ],
        ]);
    }
}
