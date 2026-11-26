<?php

namespace Tests\Funcionales;

use App\Modelos\Modelo_Configuracion;
use App\Modelos\Modelo_Solicitud_Producto;
use App\Modelos\Modelo_Unidad_Negocio;
use App\Modelos\Modelo_Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Pagina_Inertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\Caso_Prueba_Base;

class Prueba_Ecosistema extends Caso_Prueba_Base
{
    use RefreshDatabase;

    #[Test]
    public function El_Ecosistema_Resalta_Lo_Contratado_Y_Oculta_Lo_No_Publicado(): void
    {
        $Usuario = $this->Crear_Cliente($this->Crear_Institucion(['BCS']), Modelo_Usuario::Rol_Usuario_Institucional);
        Modelo_Unidad_Negocio::create(['codigo' => 'NEXUM', 'nombre' => 'Formación', 'subtitulo' => 'NEXUM', 'enlace_pagina' => 'https://nexum.example.com/']);
        $Oculto = Modelo_Unidad_Negocio::create(['codigo' => 'OCULTO', 'nombre' => 'Oculto', 'esta_publicado' => false]);

        $this->actingAs($Usuario)->get('/ecosistema')->assertOk()->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina
            ->component('Portal/Pagina_Ecosistema')->has('Productos', 2)
            ->where('Productos.0.Codigo', 'BCS')->where('Productos.0.Esta_Contratado', true)
            ->where('Productos.1.Codigo', 'NEXUM')->where('Productos.1.Esta_Contratado', false)
            ->where('Puede_Solicitar', false));
        $this->actingAs($Usuario)->get("/ecosistema/{$Oculto->id}")->assertNotFound();
    }

    #[Test]
    public function El_Detalle_Muestra_La_Pagina_O_Si_No_Hay_El_Video(): void
    {
        $Usuario = $this->Crear_Cliente();
        $Con_Pagina = Modelo_Unidad_Negocio::create(['codigo' => 'LABS', 'nombre' => 'Advisory', 'descripcion' => 'Estrategia',
            'enlace_pagina' => 'https://labs.example.com/', 'enlace_video' => 'https://youtu.be/dQw4w9WgXcQ']);
        $Con_Video = Modelo_Unidad_Negocio::create(['codigo' => 'W3M', 'nombre' => 'Marketing', 'enlace_video' => 'https://youtu.be/dQw4w9WgXcQ']);

        $this->actingAs($Usuario)->get("/ecosistema/{$Con_Pagina->id}")->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina
            ->component('Portal/Pagina_Detalle_Producto')->where('Producto.Tipo_Vista', 'pagina')
            ->where('Producto.Enlace_Pagina', 'https://labs.example.com/')->where('Producto.Descripcion', 'Estrategia'));
        $this->actingAs($Usuario)->get("/ecosistema/{$Con_Video->id}")->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina
            ->where('Producto.Tipo_Vista', 'video')->where('Producto.Url_Video', fn ($Url) => str_contains($Url, 'youtube-nocookie.com/embed/dQw4w9WgXcQ')));

        // La página del producto queda permitida en la CSP de producción
        $this->assertContains('https://labs.example.com', Modelo_Unidad_Negocio::Obtener_Origenes_Iframe());
    }

    #[Test]
    public function Solo_El_Administrador_Delegado_Solicita_Y_Se_Avisa_Por_Correo(): void
    {
        Modelo_Configuracion::where('clave', Modelo_Configuracion::Clave_Correo_Contacto)->update(['valor' => 'contacto@blockfinity.test']);
        $Institucion = $this->Crear_Institucion(['BCS']);
        $Ejecutivo = $this->Crear_Usuario_Ejecutivo($Institucion);
        $Administrador = $this->Crear_Cliente($Institucion->fresh());
        $Basico = $this->Crear_Cliente($Institucion, Modelo_Usuario::Rol_Usuario_Institucional);
        $Producto = Modelo_Unidad_Negocio::create(['codigo' => 'NEXUM', 'nombre' => 'Formación']);
        $Contratado = Modelo_Unidad_Negocio::where('codigo', 'BCS')->first();

        $this->actingAs($Basico)->post("/ecosistema/{$Producto->id}/solicitar")->assertRedirect('/');
        $this->actingAs($Administrador)->post("/ecosistema/{$Contratado->id}/solicitar")->assertSessionHas('Mensaje_Exito');
        $this->actingAs($Administrador)->post("/ecosistema/{$Producto->id}/solicitar", ['comentario' => 'Para 20 personas'])->assertSessionHas('Mensaje_Exito');
        $this->actingAs($Administrador)->post("/ecosistema/{$Producto->id}/solicitar");

        $this->assertSame(1, Modelo_Solicitud_Producto::count());
        $this->assertDatabaseHas('solicitudes_productos', ['unidad_negocio_id' => $Producto->id, 'estado' => 'pendiente', 'comentario' => 'Para 20 personas']);
        // MAIL_MAILER=array en pruebas: el correo sale a contacto y al ejecutivo a cargo, con respuesta al solicitante
        $Correos = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $Correos);
        $Correo = $Correos->first()->getOriginalMessage();
        $this->assertEqualsCanonicalizing(['contacto@blockfinity.test', $Ejecutivo->email], array_map(fn ($Direccion) => $Direccion->getAddress(), $Correo->getTo()));
        $this->assertSame($Administrador->email, $Correo->getReplyTo()[0]->getAddress());
        $this->assertStringContainsString('Para 20 personas', $Correo->getTextBody());
        $this->actingAs($Administrador)->get('/ecosistema')->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina
            ->where('Productos.1.Esta_Solicitado', true)->where('Puede_Solicitar', true));

        // El ejecutivo lo ve y, al habilitarlo en la institución, la solicitud queda atendida y sube el nivel
        $this->actingAs($Ejecutivo)->get('/admin/solicitudes-productos')->assertOk()->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina->has('Filas.data', 1));
        $this->actingAs($Ejecutivo)->post("/admin/instituciones/{$Institucion->id}", [
            'nombre' => $Institucion->nombre, 'identificador' => $Institucion->identificador, 'esta_activa' => 1,
            'color_primario' => '#1F3F73', 'color_secundario' => '#5AA9E6',
            'unidades_contratadas' => [$Contratado->id => ['activa' => 1], $Producto->id => ['activa' => 1]],
        ])->assertRedirect('/admin/instituciones');
        $this->assertDatabaseHas('solicitudes_productos', ['unidad_negocio_id' => $Producto->id, 'estado' => 'habilitado']);
        $this->assertSame('aliado', $Institucion->fresh()->Calcular_Nivel_Alianza()['Nivel_Actual']['Clave']);
    }

    #[Test]
    public function El_Superadmin_Carga_Productos_Con_Imagen_Y_Solo_Paginas_Https(): void
    {
        Storage::fake('local');
        $Superadmin = $this->Crear_Superadmin();
        $Datos = ['nombre' => 'Custodia', 'codigo' => 'CUSTODIA', 'subtitulo' => 'Custodia institucional', 'orden' => 7, 'esta_publicado' => 1];

        $this->actingAs($Superadmin)->post('/admin/ecosistema', $Datos + ['enlace_pagina' => 'http://inseguro.example.com'])->assertSessionHasErrors('enlace_pagina');
        $this->actingAs($Superadmin)->post('/admin/ecosistema', $Datos + ['enlace_pagina' => 'https://custodia.example.com',
            'ruta_imagen' => UploadedFile::fake()->image('custodia.png', 1200, 675)])->assertRedirect('/admin/ecosistema');

        $Producto = Modelo_Unidad_Negocio::where('codigo', 'CUSTODIA')->firstOrFail();
        $this->actingAs($this->Crear_Cliente())->get("/ecosistema/{$Producto->id}/imagen")->assertOk();
        $this->actingAs($this->Crear_Usuario_Ejecutivo())->get('/admin/ecosistema')->assertForbidden();
    }
}
