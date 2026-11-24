<?php

namespace Tests\Funcionales;

use App\Modelos\Modelo_Institucion;
use App\Modelos\Modelo_Unidad_Negocio;
use App\Modelos\Modelo_Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Pagina_Inertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\Caso_Prueba_Base;

class Prueba_Credencial_Y_Academia extends Caso_Prueba_Base
{
    use RefreshDatabase;

    #[Test]
    public function La_Verificacion_Publica_Refleja_La_Revocacion(): void
    {
        $Usuario = $this->Crear_Cliente();
        $this->assertNotNull($Usuario->codigo_credencial);

        $this->get("/verificar/{$Usuario->codigo_credencial}")
            ->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina->component('Publico/Pagina_Verificar_Credencial')->where('Credencial.Es_Valida', true));

        $Usuario->update(['esta_activo' => false]);
        $this->get("/verificar/{$Usuario->codigo_credencial}")
            ->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina->where('Credencial.Es_Valida', false));
    }

    #[Test]
    public function Marcar_Video_Visto_Da_Monedas_Una_Sola_Vez(): void
    {
        $Usuario = $this->Crear_Cliente();
        $Video = Modelo_Video::create(['titulo' => 'Píldora', 'formato' => 'pildora', 'puntos' => 10,
            'enlace_original' => 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view']);

        $this->assertSame('drive', $Video->proveedor);
        $this->actingAs($Usuario)->get("/academia/{$Video->id}")
            ->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina->where('Video.Url_Embebida', 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/preview'));

        $this->actingAs($Usuario)->post("/academia/{$Video->id}/visto");
        $this->actingAs($Usuario)->post("/academia/{$Video->id}/visto");
        $this->assertSame(10, $Usuario->Calcular_Saldo_Monedas());
    }

    #[Test]
    public function Video_Exclusivo_No_Se_Ve_Desde_Otra_Institucion(): void
    {
        $Video = Modelo_Video::create(['titulo' => 'Privado', 'institucion_id' => $this->Crear_Institucion()->id]);
        $this->actingAs($this->Crear_Cliente())->get("/academia/{$Video->id}")->assertNotFound();
    }

    #[Test]
    public function El_Cms_Rechaza_Enlaces_Desconocidos_Y_Guarda_Los_Validos(): void
    {
        $Superadmin = $this->Crear_Superadmin();
        $this->actingAs($Superadmin)->post('/admin/videos', ['formato' => 'grabado', 'tipo_contenido' => 'video', 'titulo' => 'X', 'enlace_original' => 'https://example.com/v.mp4'])
            ->assertSessionHasErrors('enlace_original');

        $this->actingAs($Superadmin)->post('/admin/videos', ['formato' => 'grabado', 'tipo_contenido' => 'video', 'titulo' => 'Y', 'enlace_original' => 'https://youtu.be/dQw4w9WgXcQ', 'esta_publicado' => 1])
            ->assertRedirect('/admin/videos');
        $this->assertDatabaseHas('videos', ['titulo' => 'Y', 'proveedor' => 'youtube', 'id_externo' => 'dQw4w9WgXcQ']);
    }

    #[Test]
    public function El_Cms_Solo_Acepta_Datos_Con_El_Formato_Correcto(): void
    {
        $Superadmin = $this->Crear_Superadmin();
        $this->actingAs($Superadmin)->post('/admin/usuarios', [
            'nombre' => 'Ana 123', 'email' => 'ana@prueba.test', 'password' => 'Clave12345segura',
            'rol' => 'usuario_institucional', 'telefono' => '70O-abc', 'esta_activo' => 1,
        ])->assertSessionHasErrors(['nombre', 'telefono', 'institucion_id' => 'Complete el campo institución.']);

        $this->actingAs($Superadmin)->post('/admin/instituciones', [
            'nombre' => 'Banco X', 'identificador' => 'Banco X!', 'nit' => '12A45',
        ])->assertSessionHasErrors(['identificador', 'nit']);

        $this->actingAs($Superadmin)->post('/admin/usuarios', [
            'nombre' => 'Ana María Pérez', 'email' => 'ana@prueba.test', 'password' => 'Clave12345segura',
            'rol' => 'usuario_institucional', 'institucion_id' => $this->Crear_Institucion()->id, 'telefono' => '+59170000000', 'esta_activo' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/usuarios');
    }

    #[Test]
    public function El_Cms_Crea_Institucion_Con_Unidades_Contratadas(): void
    {
        $Superadmin = $this->Crear_Superadmin();
        $Unidad = Modelo_Unidad_Negocio::create(['codigo' => 'BCS', 'nombre' => 'Compliance']);
        $this->actingAs($Superadmin)->post('/admin/instituciones', [
            'nombre' => 'Cooperativa X', 'identificador' => 'coop-x', 'esta_activa' => 1, 'color_primario' => '#1F3F73', 'color_secundario' => '#5AA9E6',
            'unidades_contratadas' => [$Unidad->id => ['activa' => 1, 'nota' => '10 licencias']],
        ])->assertRedirect('/admin/instituciones');

        $Institucion = Modelo_Institucion::where('identificador', 'coop-x')->firstOrFail();
        $this->assertTrue($Institucion->Tiene_Unidad_Contratada('BCS'));
    }
}
