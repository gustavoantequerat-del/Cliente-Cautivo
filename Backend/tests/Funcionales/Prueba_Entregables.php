<?php

namespace Tests\Funcionales;

use App\Modelos\Modelo_Entregable;
use App\Modelos\Modelo_Institucion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Caso_Prueba_Base;

class Prueba_Entregables extends Caso_Prueba_Base
{
    use RefreshDatabase;

    private function Crear_Entregable_Pendiente(Modelo_Institucion $Institucion): Modelo_Entregable
    {
        return Modelo_Entregable::create([
            'institucion_id' => $Institucion->id, 'titulo' => 'Informe v2', 'estado' => 'pendiente_aprobacion',
            'codigo_verificacion' => hash('sha256', 'contenido'), 'enlace_externo' => 'https://drive.google.com/x',
        ]);
    }

    #[Test]
    public function Aprobar_Exige_La_Contrasena_Correcta(): void
    {
        $Usuario = $this->Crear_Cliente();
        $Entregable = $this->Crear_Entregable_Pendiente($Usuario->Institucion);

        $this->actingAs($Usuario)->post("/entregables/{$Entregable->id}/aprobar", ['password' => 'incorrecta'])
            ->assertSessionHasErrors('password');
        $this->assertSame('pendiente_aprobacion', $Entregable->fresh()->estado);
    }

    #[Test]
    public function La_Aprobacion_Queda_Registrada_Con_Codigo(): void
    {
        $Usuario = $this->Crear_Cliente();
        $Entregable = $this->Crear_Entregable_Pendiente($Usuario->Institucion);

        $this->actingAs($Usuario)->post("/entregables/{$Entregable->id}/aprobar", ['password' => self::Contrasena_Prueba])
            ->assertSessionHas('Mensaje_Exito');

        $Entregable->refresh();
        $this->assertSame('aprobado', $Entregable->estado);
        $this->assertSame($Usuario->id, $Entregable->aprobado_por);
        $this->assertNotNull($Entregable->ip_aprobacion);
        $this->assertDatabaseHas('registros_auditoria', ['accion' => 'entregable.aprobar', 'sujeto_id' => $Entregable->id]);
    }

    #[Test]
    public function No_Se_Aprueba_De_Otra_Institucion_Ni_Se_Descargan_Borradores(): void
    {
        $Usuario = $this->Crear_Cliente();
        $Entregable_Ajeno = $this->Crear_Entregable_Pendiente($this->Crear_Institucion());
        $this->actingAs($Usuario)->post("/entregables/{$Entregable_Ajeno->id}/aprobar", ['password' => self::Contrasena_Prueba])->assertNotFound();

        $Borrador = Modelo_Entregable::create(['institucion_id' => $Usuario->institucion_id, 'titulo' => 'B', 'estado' => 'borrador']);
        $this->actingAs($Usuario)->get("/entregables/{$Borrador->id}/descargar")->assertNotFound();
    }

    #[Test]
    public function Verificar_Codigo_Solo_Encuentra_Documentos_Propios(): void
    {
        $Usuario = $this->Crear_Cliente();
        $this->Crear_Entregable_Pendiente($Usuario->Institucion);
        $Codigo = hash('sha256', 'contenido');

        $this->actingAs($Usuario)->post('/entregables/verificar', ['codigo_verificacion' => $Codigo])
            ->assertSessionHas('Resultado_Verificacion', fn ($Resultado) => $Resultado['Es_Autentico'] === true);

        $Otro_Usuario = $this->Crear_Cliente();
        $this->actingAs($Otro_Usuario)->post('/entregables/verificar', ['codigo_verificacion' => $Codigo])
            ->assertSessionHas('Resultado_Verificacion', fn ($Resultado) => $Resultado['Es_Autentico'] === false);

        $this->actingAs($Usuario)->post('/entregables/verificar', ['codigo_verificacion' => 'no-es-codigo'])->assertSessionHasErrors('codigo_verificacion');
    }
}
