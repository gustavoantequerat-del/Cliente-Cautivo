<?php

namespace App\Controladores\Publico;

use App\Modelos\Modelo_Usuario;
use Inertia\Inertia;
use Inertia\Response;

/** Página pública a la que apunta el QR de la credencial */
class Controlador_Verificar_Credencial
{
    public function Verificar_Credencial(string $Codigo_Credencial): Response
    {
        $Usuario = Modelo_Usuario::with('Institucion')->where('codigo_credencial', $Codigo_Credencial)->first();

        return Inertia::render('Publico/Pagina_Verificar_Credencial', [
            'Credencial' => $Usuario ? [
                'Es_Valida' => $Usuario->esta_activo && $Usuario->Institucion?->esta_activa,
                'Nombre' => $Usuario->nombre,
                'Area' => $Usuario->area,
                'Institucion' => $Usuario->Institucion?->nombre,
                'Numero' => $Usuario->numero_credencial,
                'Fecha_Emision' => $Usuario->fecha_emision_credencial?->format('d/m/Y'),
            ] : null,
        ]);
    }
}
