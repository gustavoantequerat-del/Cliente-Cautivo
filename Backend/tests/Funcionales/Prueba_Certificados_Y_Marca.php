<?php

namespace Tests\Funcionales;

use App\Modelos\Modelo_Certificado;
use App\Modelos\Modelo_Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Pagina_Inertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\Caso_Prueba_Base;

class Prueba_Certificados_Y_Marca extends Caso_Prueba_Base
{
    use RefreshDatabase;

    #[Test]
    public function El_Ejecutivo_Sube_Certificados_Solo_A_Personas_De_Sus_Instituciones(): void
    {
        Storage::fake('local');
        $Mi_Institucion = $this->Crear_Institucion();
        $Ejecutivo = $this->Crear_Usuario_Ejecutivo($Mi_Institucion);
        $Persona = $this->Crear_Cliente($Mi_Institucion, Modelo_Usuario::Rol_Usuario_Institucional);
        $Persona_Ajena = $this->Crear_Cliente(null, Modelo_Usuario::Rol_Usuario_Institucional);
        $Datos = ['titulo' => 'NEXUM Nivel 1', 'descripcion' => 'Aprobó el programa', 'puntaje' => 40];

        $this->actingAs($Ejecutivo)->post('/admin/certificados', $Datos + ['usuario_id' => $Persona->id])
            ->assertSessionHasErrors('ruta_archivo');
        $this->actingAs($Ejecutivo)->post('/admin/certificados', $Datos + ['usuario_id' => $Persona_Ajena->id,
            'ruta_archivo' => UploadedFile::fake()->create('c.pdf', 20, 'application/pdf')])->assertSessionHasErrors('usuario_id');
        $this->actingAs($Ejecutivo)->post('/admin/certificados', $Datos + ['usuario_id' => $Persona->id,
            'ruta_archivo' => UploadedFile::fake()->create('nexum.pdf', 20, 'application/pdf')])->assertRedirect('/admin/certificados');

        $Certificado = Modelo_Certificado::firstOrFail();
        $this->assertEquals($Mi_Institucion->id, $Certificado->institucion_id);
        $this->assertSame(40, $Certificado->puntaje);

        // La persona lo ve y lo abre; otra persona de la misma institución, no
        $this->actingAs($Persona)->get('/certificados')->assertOk()
            ->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina->has('Mis_Certificados', 1)->where('Certificados_Del_Equipo', null));
        $this->actingAs($Persona)->get("/certificados/{$Certificado->id}/archivo")->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $Companero = $this->Crear_Cliente($Mi_Institucion, Modelo_Usuario::Rol_Usuario_Institucional);
        $this->actingAs($Companero)->get("/certificados/{$Certificado->id}/archivo")->assertNotFound();

        // Su administrador delegado ve los de su equipo
        $Administrador = $this->Crear_Cliente($Mi_Institucion, Modelo_Usuario::Rol_Administrador_Delegado);
        $this->actingAs($Administrador)->get('/certificados')->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina->has('Certificados_Del_Equipo', 1));
        $this->actingAs($Administrador)->get("/certificados/{$Certificado->id}/archivo")->assertOk();
    }

    #[Test]
    public function El_Portal_Usa_Los_Colores_Y_El_Logo_De_La_Institucion(): void
    {
        Storage::fake('local');
        $Institucion = $this->Crear_Institucion();
        $Superadmin = $this->Crear_Superadmin();
        $this->actingAs($Superadmin)->post("/admin/instituciones/{$Institucion->id}", [
            'nombre' => $Institucion->nombre, 'identificador' => $Institucion->identificador, 'esta_activa' => 1,
            'color_primario' => '#0E6B4F', 'color_secundario' => '#F2C94C', 'ruta_logo' => UploadedFile::fake()->image('logo.png', 200, 80),
        ])->assertRedirect('/admin/instituciones');

        $Usuario = $this->Crear_Cliente($Institucion->fresh(), Modelo_Usuario::Rol_Usuario_Institucional);
        $this->actingAs($Usuario)->get('/')->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina
            ->where('Institucion_Actual.Color_Primario', '#0E6B4F')
            ->where('Institucion_Actual.Color_Secundario', '#F2C94C')
            ->where('Institucion_Actual.Url_Logo', fn ($Url) => str_starts_with($Url, '/marca/logo')));
        $this->actingAs($Usuario)->get('/marca/logo')->assertOk();

        // Un SVG como logo no se acepta (podría llevar código)
        $this->actingAs($Superadmin)->post("/admin/instituciones/{$Institucion->id}", [
            'nombre' => $Institucion->nombre, 'identificador' => $Institucion->identificador, 'color_primario' => '#0E6B4F',
            'color_secundario' => '#F2C94C', 'ruta_logo' => UploadedFile::fake()->create('logo.svg', 5, 'image/svg+xml'),
        ])->assertSessionHasErrors('ruta_logo');
    }
}
