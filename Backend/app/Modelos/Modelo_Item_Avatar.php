<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Objeto de la Forja. El "estilo" es "tipo.variante" y cada variante tiene su dibujo en
 * Frontend/Utilidades/Dibujo_Avatar.js: si se agrega una aquí, hay que dibujarla allá.
 */
class Modelo_Item_Avatar extends Model
{
    public const Tipos = [
        'camisa' => 'Camisa', 'pantalon' => 'Pantalones', 'zapato' => 'Zapatos', 'gorra' => 'Gorra',
        'extra' => 'Extra (en la mano)', 'accesorio' => 'Accesorio', 'pose' => 'Pose',
    ];

    public const Estilos = [
        'camisa.manga_corta' => 'Camisa · Manga corta', 'camisa.manga_larga' => 'Camisa · Manga larga',
        'camisa.polo' => 'Camisa · Polo', 'camisa.chaleco' => 'Camisa · Chaleco', 'camisa.saco' => 'Camisa · Saco',
        'pantalon.largo' => 'Pantalones · Largo', 'pantalon.corto' => 'Pantalones · Corto', 'pantalon.falda' => 'Pantalones · Falda',
        'zapato.zapatilla' => 'Zapatos · Zapatillas', 'zapato.bota' => 'Zapatos · Botas', 'zapato.formal' => 'Zapatos · Formales',
        'gorra.gorra' => 'Gorra · Gorra', 'gorra.sombrero' => 'Gorra · Sombrero', 'gorra.gorro' => 'Gorra · Gorro de lana', 'gorra.corona' => 'Gorra · Corona',
        'extra.taza' => 'Extra · Taza', 'extra.laptop' => 'Extra · Laptop', 'extra.libro' => 'Extra · Libro',
        'extra.bandera' => 'Extra · Bandera', 'extra.celular' => 'Extra · Celular', 'extra.trofeo' => 'Extra · Trofeo',
        'accesorio.mochila' => 'Accesorio · Mochila', 'accesorio.capa' => 'Accesorio · Capa', 'accesorio.bufanda' => 'Accesorio · Bufanda',
        'accesorio.corbata' => 'Accesorio · Corbata', 'accesorio.medalla' => 'Accesorio · Medalla',
        'pose.saludo' => 'Pose · Saludo', 'pose.brazos_arriba' => 'Pose · Brazos arriba', 'pose.manos_cintura' => 'Pose · Manos en la cintura',
        'pose.pensando' => 'Pose · Pensando', 'pose.senalando' => 'Pose · Señalando', 'pose.baile' => 'Pose · Baile',
    ];

    /** Cuántos objetos de un tipo se pueden llevar a la vez (por defecto 1) */
    public const Maximo_Equipados = ['extra' => 2];

    protected $table = 'items_avatar';

    protected $fillable = ['nombre', 'estilo', 'color_principal', 'color_secundario', 'precio', 'detalle', 'esta_publicado'];

    protected function casts(): array
    {
        return ['esta_publicado' => 'boolean', 'precio' => 'integer'];
    }

    /** El tipo sale del estilo elegido en la Forja */
    protected static function booted(): void
    {
        static::saving(fn (Modelo_Item_Avatar $Item) => $Item->tipo = Str::before($Item->estilo, '.'));
    }

    /** Lo que necesita Dibujo_Avatar.js para dibujarlo */
    public function Formatear_Para_Dibujo(): array
    {
        return [
            'Tipo' => $this->tipo,
            'Estilo' => Str::after($this->estilo, '.'),
            'Color_Principal' => $this->color_principal,
            'Color_Secundario' => $this->color_secundario,
        ];
    }
}
