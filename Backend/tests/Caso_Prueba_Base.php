<?php

namespace Tests;

use App\Modelos\Modelo_Institucion;
use App\Modelos\Modelo_Unidad_Negocio;
use App\Modelos\Modelo_Usuario;
use Illuminate\Foundation\Testing\TestCase;

abstract class Caso_Prueba_Base extends TestCase
{
    protected const Contrasena_Prueba = 'password123abc';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function Crear_Institucion(array $Codigos_Unidades = ['BCS']): Modelo_Institucion
    {
        $Institucion = Modelo_Institucion::create(['nombre' => 'Banco '.uniqid(), 'identificador' => uniqid('b-')]);
        foreach ($Codigos_Unidades as $Codigo) {
            $Unidad = Modelo_Unidad_Negocio::firstOrCreate(['codigo' => $Codigo], ['nombre' => $Codigo]);
            $Institucion->Unidades_Contratadas()->attach($Unidad->id);
        }

        return $Institucion;
    }

    protected function Crear_Cliente(?Modelo_Institucion $Institucion = null, string $Rol = Modelo_Usuario::Rol_Funcionario): Modelo_Usuario
    {
        return $this->Crear_Usuario(['institucion_id' => ($Institucion ?? $this->Crear_Institucion())->id, 'rol' => $Rol]);
    }

    protected function Crear_Staff(): Modelo_Usuario
    {
        return $this->Crear_Usuario(['rol' => Modelo_Usuario::Rol_Staff]);
    }

    private function Crear_Usuario(array $Datos): Modelo_Usuario
    {
        return Modelo_Usuario::create($Datos + [
            'nombre' => 'Usuario de prueba',
            'email' => uniqid('usuario-').'@prueba.test',
            'password' => self::Contrasena_Prueba,
            'area' => 'Gerencia de Riesgos',
            'esta_activo' => true,
        ]);
    }
}
