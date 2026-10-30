<?php

namespace App\Modelos\Rasgos;

use App\Modelos\Modelo_Institucion;
use App\Modelos\Modelo_Usuario;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aísla los datos por institución: un cliente solo ve lo de su entidad.
 */
trait Rasgo_Exclusivo_De_Institucion
{
    public function Institucion(): BelongsTo
    {
        return $this->belongsTo(Modelo_Institucion::class, 'institucion_id');
    }

    #[Scope]
    protected function Visibles_Para_Usuario(Builder $Consulta, Modelo_Usuario $Usuario): void
    {
        $Consulta->where($this->getTable().'.institucion_id', $Usuario->institucion_id);
    }
}
