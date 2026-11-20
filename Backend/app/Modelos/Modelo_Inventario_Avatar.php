<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Modelo_Inventario_Avatar extends Model
{
    protected $table = 'inventario_avatar';

    protected $fillable = ['usuario_id', 'item_avatar_id', 'esta_equipado'];

    protected function casts(): array
    {
        return ['esta_equipado' => 'boolean'];
    }

    public function Item(): BelongsTo
    {
        return $this->belongsTo(Modelo_Item_Avatar::class, 'item_avatar_id');
    }
}
