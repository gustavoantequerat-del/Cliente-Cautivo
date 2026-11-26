<?php

namespace Tests\Funcionales;

use App\Modelos\Modelo_Configuracion;
use App\Modelos\Modelo_Reporte_Research;
use App\Modelos\Modelo_Unidad_Negocio;
use App\Modelos\Modelo_Usuario;
use App\Modelos\Modelo_Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Pagina_Inertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\Caso_Prueba_Base;

class Prueba_Niveles_Y_Research extends Caso_Prueba_Base
{
    use RefreshDatabase;

    #[Test]
    public function El_Nivel_Depende_De_Los_Servicios_Contratados(): void
    {
        foreach (['BCS', 'NEXUM', 'RESEARCH', 'LAB'] as $Codigo) {
            Modelo_Unidad_Negocio::create(['codigo' => $Codigo, 'nombre' => $Codigo]);
        }
        $Explorador = $this->Crear_Institucion(['BCS'])->Calcular_Nivel_Alianza();
        $this->assertSame('Cliente Explorador', $Explorador['Nivel_Actual']['Nombre']);
        $this->assertSame(1, $Explorador['Servicios_Faltantes']);

        $Aliado = $this->Crear_Institucion(['BCS', 'NEXUM'])->Calcular_Nivel_Alianza();
        $this->assertSame('Aliado Institucional', $Aliado['Nivel_Actual']['Nombre']);
        $this->assertSame('premium', $Aliado['Nivel_Siguiente']['Clave']);

        // Premium desde 3 productos o servicios (no hace falta tenerlos todos)
        foreach ([['BCS', 'NEXUM', 'RESEARCH'], ['BCS', 'NEXUM', 'RESEARCH', 'LAB']] as $Codigos) {
            $Premium = $this->Crear_Institucion($Codigos)->Calcular_Nivel_Alianza();
            $this->assertSame('premium', $Premium['Nivel_Actual']['Clave']);
            $this->assertNull($Premium['Nivel_Siguiente']);
            $this->assertSame(0, $Premium['Servicios_Faltantes']);
        }
        $this->assertCount(3, $Premium['Niveles']);
    }

    #[Test]
    public function El_Reporte_Se_Lee_Con_El_Visor_De_Google_Drive(): void
    {
        $Usuario = $this->Crear_Cliente($this->Crear_Institucion(['RESEARCH']), Modelo_Usuario::Rol_Usuario_Institucional);
        $Reporte = Modelo_Reporte_Research::create(['titulo' => 'Pulse', 'tema' => 'Regulación',
            'enlace_externo' => 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view?usp=sharing']);

        $this->actingAs($Usuario)->get("/research/{$Reporte->id}")->assertOk()->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina
            ->component('Portal/Pagina_Detalle_Reporte')
            ->where('Reporte.Documento.Url_Visor', 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/preview')
            ->where('Reporte.Documento.Url_Abrir', 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view'));
        // Sin Research contratado no se ve
        $this->actingAs($this->Crear_Cliente($this->Crear_Institucion(['BCS'])))->get("/research/{$Reporte->id}")->assertRedirect('/');
    }

    #[Test]
    public function Una_Pildora_En_Pdf_Se_Lee_Con_El_Visor_De_Google_Drive(): void
    {
        $Superadmin = $this->Crear_Superadmin();
        $this->actingAs($Superadmin)->post('/admin/videos', ['formato' => 'pildora', 'titulo' => 'Guía Travel Rule', 'tipo_contenido' => 'pdf',
            'esta_publicado' => 1, 'enlace_externo' => 'https://example.com/guia.pdf'])->assertSessionHasErrors('enlace_externo');
        $this->actingAs($Superadmin)->post('/admin/videos', ['formato' => 'pildora', 'titulo' => 'Guía Travel Rule', 'tipo_contenido' => 'pdf',
            'esta_publicado' => 1, 'enlace_externo' => 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view'])->assertRedirect('/admin/videos');
        $Pildora = Modelo_Video::where('titulo', 'Guía Travel Rule')->firstOrFail();
        $Usuario = $this->Crear_Cliente(null, Modelo_Usuario::Rol_Usuario_Institucional);

        $this->actingAs($Usuario)->get("/academia/{$Pildora->id}")->assertOk()->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina
            ->where('Video.Tipo_Contenido', 'pdf')->where('Video.Documento.Url_Visor', 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/preview'));
        $this->actingAs($Usuario)->post("/academia/{$Pildora->id}/visto")->assertSessionHas('Mensaje_Exito');
        $this->assertDatabaseHas('progreso_videos', ['video_id' => $Pildora->id, 'usuario_id' => $Usuario->id]);
    }

    #[Test]
    public function Solo_El_Superadmin_Edita_La_Configuracion(): void
    {
        $Superadmin = $this->Crear_Superadmin();
        $Ajuste = Modelo_Configuracion::firstOrFail();
        $this->actingAs($Superadmin)->post("/admin/configuracion/{$Ajuste->id}", ['valor' => 'Uso interno · {institucion}'])->assertRedirect('/admin/configuracion');
        $this->assertSame('Uso interno · {institucion}', $Ajuste->fresh()->valor);
        $this->actingAs($Superadmin)->get('/admin/configuracion/crear')->assertForbidden();

        $Ejecutivo = $this->Crear_Usuario_Ejecutivo($this->Crear_Institucion());
        $this->actingAs($Ejecutivo)->get('/admin/configuracion')->assertForbidden();
    }
}
