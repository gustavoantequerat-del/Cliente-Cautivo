<?php

namespace App\Controladores\Administracion;

use App\Modelos\Modelo_Registro_Auditoria;
use Inertia\Inertia;
use Inertia\Response;

class Controlador_Auditoria
{
    public function Listar_Registros_Auditoria(): Response
    {
        return Inertia::render('Administracion/Pagina_Auditoria', [
            'Registros' => Modelo_Registro_Auditoria::with('Usuario')->latest('created_at')->paginate(40)
                ->through(fn (Modelo_Registro_Auditoria $Registro) => [
                    'Id' => $Registro->id,
                    'Fecha' => $Registro->created_at->format('d/m/Y H:i'),
                    'Usuario' => $Registro->Usuario?->nombre ?? '—',
                    'Accion' => $Registro->accion,
                    'Descripcion' => $Registro->descripcion,
                    'Codigo_Verificacion' => $Registro->datos_extra['Codigo_Verificacion'] ?? null,
                    'Ip' => $Registro->ip,
                ]),
        ]);
    }
}
