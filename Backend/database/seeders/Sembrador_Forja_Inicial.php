<?php

namespace Database\Seeders;

use App\Modelos\Modelo_Item_Avatar;
use Illuminate\Database\Seeder;

/** Catálogo inicial de la Forja. Seguro para producción: php artisan db:seed --class=Sembrador_Forja_Inicial */
class Sembrador_Forja_Inicial extends Seeder
{
    public function run(): void
    {
        $Objetos = [
            // [nombre, estilo, color principal, color de detalles, precio]
            ['Camiseta Blockfinity', 'camisa.manga_corta', '#1F3F73', '#FFFFFF', 20],
            ['Camisa de trabajo', 'camisa.manga_larga', '#E3E9F2', '#3B5B8C', 40],
            ['Polo verde', 'camisa.polo', '#3E9B66', '#FFFFFF', 40],
            ['Chaleco ejecutivo', 'camisa.chaleco', '#2B3038', '#CFE0F5', 60],
            ['Saco de directorio', 'camisa.saco', '#0F2240', '#FFFFFF', 120],
            ['Jeans', 'pantalon.largo', '#3B5B8C', '#2B3038', 30],
            ['Pantalón de vestir', 'pantalon.largo', '#2B3038', '#111111', 50],
            ['Short de verano', 'pantalon.corto', '#5AA9E6', '#1F3F73', 25],
            ['Falda ejecutiva', 'pantalon.falda', '#4B3A9C', '#2B3038', 50],
            ['Zapatillas blancas', 'zapato.zapatilla', '#FFFFFF', '#C0392B', 25],
            ['Botas de explorador', 'zapato.bota', '#5A3A20', '#2B2B2B', 45],
            ['Zapatos formales', 'zapato.formal', '#2B2018', '#FFFFFF', 60],
            ['Gorra roja', 'gorra.gorra', '#C0392B', '#8E2A20', 30],
            ['Sombrero de campo', 'gorra.sombrero', '#C9A06A', '#5A3A20', 50],
            ['Gorro de lana', 'gorra.gorro', '#3B5B8C', '#FFFFFF', 35],
            ['Corona de socio', 'gorra.corona', '#F2C94C', '#C0392B', 300],
            ['Taza de café', 'extra.taza', '#FFFFFF', '#8A93A0', 15],
            ['Laptop', 'extra.laptop', '#5F6773', '#CFE0F5', 60],
            ['Libro de normativa', 'extra.libro', '#3E9B66', '#1D5B3A', 30],
            ['Bandera de Bolivia', 'extra.bandera', '#C0392B', '#8A93A0', 40],
            ['Celular', 'extra.celular', '#2B3038', '#5AA9E6', 30],
            ['Trofeo', 'extra.trofeo', '#F2C94C', '#5F6773', 200],
            ['Mochila', 'accesorio.mochila', '#C0392B', '#2B3038', 40],
            ['Capa de héroe', 'accesorio.capa', '#C0392B', '#F2C94C', 150],
            ['Bufanda', 'accesorio.bufanda', '#C0392B', '#FFFFFF', 30],
            ['Corbata', 'accesorio.corbata', '#C0392B', '#8E2A20', 30],
            ['Medalla de oro', 'accesorio.medalla', '#F2C94C', '#C0392B', 100],
            ['Saludo', 'pose.saludo', '#3B5B8C', '#FFFFFF', 20],
            ['¡Victoria!', 'pose.brazos_arriba', '#3B5B8C', '#FFFFFF', 60],
            ['Manos en la cintura', 'pose.manos_cintura', '#3B5B8C', '#FFFFFF', 40],
            ['Pensando', 'pose.pensando', '#3B5B8C', '#FFFFFF', 40],
            ['Señalando', 'pose.senalando', '#3B5B8C', '#FFFFFF', 40],
            ['Baile', 'pose.baile', '#3B5B8C', '#FFFFFF', 80],
        ];
        foreach ($Objetos as [$Nombre, $Estilo, $Color_Principal, $Color_Secundario, $Precio]) {
            Modelo_Item_Avatar::updateOrCreate(['nombre' => $Nombre], [
                'estilo' => $Estilo, 'color_principal' => $Color_Principal, 'color_secundario' => $Color_Secundario, 'precio' => $Precio,
            ]);
        }
    }
}
