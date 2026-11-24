<?php

namespace Tests\Funcionales;

use App\Modelos\Modelo_Configuracion;
use App\Modelos\Modelo_Item_Avatar;
use App\Modelos\Modelo_Movimiento_Monedas;
use App\Modelos\Modelo_Reporte_Research;
use App\Modelos\Modelo_Unidad_Negocio;
use App\Modelos\Modelo_Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Pagina_Inertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\Caso_Prueba_Base;

class Prueba_Avatar_Y_Research extends Caso_Prueba_Base
{
    use RefreshDatabase;

    private function Crear_Item(string $Estilo, int $Precio = 50): Modelo_Item_Avatar
    {
        return Modelo_Item_Avatar::create(['nombre' => $Estilo, 'estilo' => $Estilo, 'precio' => $Precio,
            'color_principal' => '#112233', 'color_secundario' => '#FFFFFF', 'esta_publicado' => true]);
    }

    private function Dar_Monedas(Modelo_Usuario $Usuario, int $Cantidad): void
    {
        Modelo_Movimiento_Monedas::Otorgar_Una_Vez($Usuario, $Cantidad, 'bono', 1, 'Prueba');
    }

    #[Test]
    public function El_Nivel_Depende_De_Los_Servicios_Contratados(): void
    {
        foreach (['BCS', 'NEXUM', 'RESEARCH'] as $Codigo) {
            Modelo_Unidad_Negocio::create(['codigo' => $Codigo, 'nombre' => $Codigo]);
        }
        $this->assertSame('explorador', $this->Crear_Institucion(['BCS'])->Calcular_Nivel_Alianza()['Nivel_Actual']['Clave']);
        $this->assertSame('aliado', $this->Crear_Institucion(['BCS', 'NEXUM'])->Calcular_Nivel_Alianza()['Nivel_Actual']['Clave']);
        $Nivel_Maximo = $this->Crear_Institucion(['BCS', 'NEXUM', 'RESEARCH'])->Calcular_Nivel_Alianza();
        $this->assertSame('socio_estrategico', $Nivel_Maximo['Nivel_Actual']['Clave']);
        $this->assertNull($Nivel_Maximo['Nivel_Siguiente']);
        $this->assertCount(3, $Nivel_Maximo['Niveles']);
    }

    #[Test]
    public function Comprar_Descuenta_Monedas_Una_Sola_Vez_Y_Exige_Saldo(): void
    {
        $Usuario = $this->Crear_Cliente(null, Modelo_Usuario::Rol_Usuario_Institucional);
        $Gorra = $this->Crear_Item('gorra.gorra', 50);

        $this->actingAs($Usuario)->post("/avatar/{$Gorra->id}/comprar")->assertSessionHas('Mensaje_Error');
        $this->Dar_Monedas($Usuario, 80);
        $this->actingAs($Usuario)->post("/avatar/{$Gorra->id}/comprar")->assertSessionHas('Mensaje_Exito');
        $this->actingAs($Usuario)->post("/avatar/{$Gorra->id}/comprar")->assertSessionHas('Mensaje_Error');
        $this->assertSame(30, $Usuario->Calcular_Saldo_Monedas());

        $this->actingAs($Usuario)->get('/avatar')->assertOk()
            ->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina->where('Saldo_Monedas', 30)->where('Items.0.Es_Propio', true));
    }

    #[Test]
    public function Un_Objeto_Por_Parte_Y_Maximo_Dos_Extras(): void
    {
        $Usuario = $this->Crear_Cliente(null, Modelo_Usuario::Rol_Usuario_Institucional);
        $this->Dar_Monedas($Usuario, 1000);
        $Items = collect(['camisa.polo', 'camisa.saco', 'extra.taza', 'extra.libro', 'extra.laptop'])->map(fn ($Estilo) => $this->Crear_Item($Estilo, 10));
        foreach ($Items as $Item) {
            $this->actingAs($Usuario)->post("/avatar/{$Item->id}/comprar");
        }

        $this->actingAs($Usuario)->post("/avatar/{$Items[0]->id}/equipar");
        $this->actingAs($Usuario)->post("/avatar/{$Items[1]->id}/equipar");
        $this->actingAs($Usuario)->post("/avatar/{$Items[2]->id}/equipar");
        $this->actingAs($Usuario)->post("/avatar/{$Items[3]->id}/equipar");
        $this->actingAs($Usuario)->post("/avatar/{$Items[4]->id}/equipar")->assertSessionHas('Mensaje_Error');

        $Estilos_Puestos = collect($Usuario->Obtener_Objetos_Equipados())->map(fn ($Objeto) => $Objeto['Tipo'].'.'.$Objeto['Estilo'])->sort()->values()->all();
        $this->assertSame(['camisa.saco', 'extra.libro', 'extra.taza'], $Estilos_Puestos);

        $Ajeno = $this->Crear_Item('gorra.corona', 10);
        $this->actingAs($Usuario)->post("/avatar/{$Ajeno->id}/equipar")->assertNotFound();
    }

    #[Test]
    public function El_Reporte_Se_Lee_En_El_Visor_Y_Da_Monedas_Una_Vez(): void
    {
        Storage::fake('local');
        Http::fake(['drive.usercontent.google.com/*' => Http::response('%PDF-1.4 contenido de prueba', 200)]);
        Modelo_Configuracion::where('clave', Modelo_Configuracion::Clave_Texto_Marca_Agua)->update(['valor' => 'Solo para {usuario}']);
        $Usuario = $this->Crear_Cliente($this->Crear_Institucion(['RESEARCH']), Modelo_Usuario::Rol_Usuario_Institucional);
        $Reporte = Modelo_Reporte_Research::create(['titulo' => 'Pulse', 'tema' => 'Regulación', 'puntos' => 15,
            'enlace_externo' => 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view']);

        $this->actingAs($Usuario)->get("/research/{$Reporte->id}")->assertOk()->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina
            ->component('Portal/Pagina_Detalle_Reporte')->where('Texto_Marca_Agua', "Solo para {$Usuario->nombre}"));
        $Documento = $this->actingAs($Usuario)->get("/research/{$Reporte->id}/documento");
        $Documento->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertHeader('Content-Disposition', 'inline');
        $this->assertStringStartsWith('%PDF', $Documento->getContent());
        Http::assertSent(fn ($Pedido) => str_contains($Pedido->url(), 'id=1AbCdEfGhIjKlMnOp'));

        $this->actingAs($Usuario)->post("/research/{$Reporte->id}/leido")->assertSessionHas('Mensaje_Exito');
        $this->actingAs($Usuario)->post("/research/{$Reporte->id}/leido");
        $this->assertSame(15, $Usuario->Calcular_Saldo_Monedas());
    }

    #[Test]
    public function Si_Drive_No_Entrega_Un_Pdf_El_Visor_Recibe_Un_Error(): void
    {
        Storage::fake('local');
        Http::fake(['drive.usercontent.google.com/*' => Http::response('<html>Necesita permiso</html>', 200)]);
        $Usuario = $this->Crear_Cliente($this->Crear_Institucion(['RESEARCH']));
        $Reporte = Modelo_Reporte_Research::create(['titulo' => 'Privado', 'tema' => 'Regulación',
            'enlace_externo' => 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view']);

        $this->actingAs($Usuario)->get("/research/{$Reporte->id}/documento")->assertStatus(502);
        $this->actingAs($this->Crear_Cliente($this->Crear_Institucion(['BCS'])))->get("/research/{$Reporte->id}/documento")->assertRedirect('/');
    }

    #[Test]
    public function Solo_El_Superadmin_Usa_La_Forja_Y_La_Configuracion(): void
    {
        $Superadmin = $this->Crear_Superadmin();
        $this->actingAs($Superadmin)->post('/admin/forja', ['nombre' => 'Capa roja', 'estilo' => 'accesorio.capa', 'color_principal' => '#C0392B',
            'color_secundario' => 'rojo', 'precio' => 100, 'esta_publicado' => 1])->assertSessionHasErrors('color_secundario');
        $this->actingAs($Superadmin)->post('/admin/forja', ['nombre' => 'Capa roja', 'estilo' => 'accesorio.capa', 'color_principal' => '#C0392B',
            'color_secundario' => '#F2C94C', 'precio' => 100, 'esta_publicado' => 1])->assertRedirect('/admin/forja');
        $this->assertDatabaseHas('items_avatar', ['nombre' => 'Capa roja', 'tipo' => 'accesorio']);

        $Ajuste = Modelo_Configuracion::firstOrFail();
        $this->actingAs($Superadmin)->post("/admin/configuracion/{$Ajuste->id}", ['valor' => 'Uso interno · {institucion}'])->assertRedirect('/admin/configuracion');
        $this->assertSame('Uso interno · {institucion}', $Ajuste->fresh()->valor);
        $this->actingAs($Superadmin)->get('/admin/configuracion/crear')->assertForbidden();

        $Ejecutivo = $this->Crear_Usuario_Ejecutivo($this->Crear_Institucion());
        $this->actingAs($Ejecutivo)->get('/admin/forja')->assertForbidden();
    }
}
