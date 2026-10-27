<?php

namespace App\Modelos\Rasgos;

use App\Modelos\Modelo_Usuario;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Contenido general (institucion_id vacío) o exclusivo de una institución.
 */
trait Rasgo_General_O_Exclusivo
{
    #[Scope]
    protected function Visibles_Para_Usuario(Builder $Consulta, Modelo_Usuario $Usuario): void
    {
        $Consulta->where(function (Builder $Condicion) use ($Usuario) {
            $Condicion->whereNull($this->getTable().'.institucion_id')
                ->orWhere($this->getTable().'.institucion_id', $Usuario->institucion_id);
        });
    }
}
