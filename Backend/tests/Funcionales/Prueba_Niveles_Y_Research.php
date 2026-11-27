<?php

namespace Tests\Funcionales;

use App\Modelos\Modelo_Configuracion;
use App\Modelos\Modelo_Reporte_Research;
use App\Modelos\Modelo_Unidad_Negocio;
use App\Modelos\Modelo_Usuario;
use App\Modelos\Modelo_Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
    public function El_Reporte_Se_Sube_Como_Pdf_Y_Se_Lee_En_El_Portal(): void
    {
        Storage::fake('local');
        $this->actingAs($this->Crear_Superadmin())->post('/admin/research', ['titulo' => 'Pulse', 'tema' => 'Regulación', 'esta_publicado' => 1, 'origen_documento' => 'archivo',
            'ruta_archivo' => UploadedFile::fake()->createWithContent('pulse t3.pdf', '%PDF-1.4 contenido')])->assertRedirect('/admin/research');
        $Reporte = Modelo_Reporte_Research::where('titulo', 'Pulse')->firstOrFail();
        $Usuario = $this->Crear_Cliente($this->Crear_Institucion(['RESEARCH']), Modelo_Usuario::Rol_Usuario_Institucional);

        $this->actingAs($Usuario)->get("/research/{$Reporte->id}")->assertOk()->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina
            ->component('Portal/Pagina_Detalle_Reporte')->where('Reporte.Documento.Url_Visor', "/research/{$Reporte->id}/documento"));
        $Documento = $this->actingAs($Usuario)->get("/research/{$Reporte->id}/documento");
        $Documento->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('inline', $Documento->headers->get('Content-Disposition'));

        // Sin Research contratado no se ve ni se abre
        $Otro = $this->Crear_Cliente($this->Crear_Institucion(['BCS']));
        $this->actingAs($Otro)->get("/research/{$Reporte->id}/documento")->assertRedirect('/');
    }

    #[Test]
    public function El_Cms_Elige_Entre_Archivo_Y_Enlace_De_Drive(): void
    {
        $Superadmin = $this->Crear_Superadmin();
        $Datos = ['titulo' => 'Pulse Drive', 'tema' => 'Regulación', 'esta_publicado' => 1, 'origen_documento' => 'drive'];
        $this->actingAs($Superadmin)->post('/admin/research', $Datos)->assertSessionHasErrors('enlace_externo');
        $this->actingAs($Superadmin)->post('/admin/research', $Datos + ['enlace_externo' => 'https://example.com/x.pdf'])->assertSessionHasErrors('enlace_externo');
        $this->actingAs($Superadmin)->post('/admin/research', $Datos + ['enlace_externo' => 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view'])
            ->assertRedirect('/admin/research');
        $Reporte = Modelo_Reporte_Research::where('titulo', 'Pulse Drive')->firstOrFail();
        $Usuario = $this->Crear_Cliente($this->Crear_Institucion(['RESEARCH']));

        $this->actingAs($Usuario)->get("/research/{$Reporte->id}")->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina
            ->where('Reporte.Documento.Url_Visor', 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/preview'));

        // Una píldora también puede usar Drive
        $this->actingAs($Superadmin)->post('/admin/videos', ['formato' => 'pildora', 'titulo' => 'Píldora Drive', 'tipo_contenido' => 'pdf',
            'origen_documento' => 'drive', 'esta_publicado' => 1, 'enlace_externo' => 'https://drive.google.com/file/d/1ZyXwVuTsRqPoNm/view'])
            ->assertRedirect('/admin/videos');
        $Pildora = Modelo_Video::where('titulo', 'Píldora Drive')->firstOrFail();
        $this->actingAs($Usuario)->get("/academia/{$Pildora->id}")->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina
            ->where('Video.Documento.Url_Visor', 'https://drive.google.com/file/d/1ZyXwVuTsRqPoNm/preview'));
    }

    #[Test]
    public function Una_Pildora_En_Pdf_Se_Sube_Y_Se_Lee_En_El_Portal(): void
    {
        Storage::fake('local');
        $this->actingAs($this->Crear_Superadmin())->post('/admin/videos', ['formato' => 'pildora', 'titulo' => 'Guía Travel Rule', 'tipo_contenido' => 'pdf',
            'origen_documento' => 'archivo', 'esta_publicado' => 1, 'ruta_archivo' => UploadedFile::fake()->createWithContent('guia.pdf', '%PDF-1.4 guía')])->assertRedirect('/admin/videos');
        $Pildora = Modelo_Video::where('titulo', 'Guía Travel Rule')->firstOrFail();
        $Usuario = $this->Crear_Cliente(null, Modelo_Usuario::Rol_Usuario_Institucional);

        $this->actingAs($Usuario)->get("/academia/{$Pildora->id}")->assertOk()->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina
            ->where('Video.Tipo_Contenido', 'pdf')->where('Video.Documento.Url_Visor', "/academia/{$Pildora->id}/documento"));
        $this->actingAs($Usuario)->get("/academia/{$Pildora->id}/documento")->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->actingAs($Usuario)->post("/academia/{$Pildora->id}/visto")->assertSessionHas('Mensaje_Exito');

        // Un video no tiene documento
        $Video = Modelo_Video::create(['titulo' => 'Video', 'formato' => 'grabado', 'esta_publicado' => true]);
        $this->actingAs($Usuario)->get("/academia/{$Video->id}/documento")->assertNotFound();
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
