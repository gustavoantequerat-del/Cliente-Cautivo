<?php

namespace Tests\Funcionales;

use App\Modelos\Modelo_Proyecto;
use App\Modelos\Modelo_Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Pagina_Inertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\Caso_Prueba_Base;

class Prueba_Acceso_Portal extends Caso_Prueba_Base
{
    use RefreshDatabase;

    #[Test]
    public function Visitante_Es_Enviado_Al_Login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertOk();
    }

    #[Test]
    public function Cliente_Ingresa_Y_Ve_El_Inicio(): void
    {
        $Usuario = $this->Crear_Cliente();
        $this->post('/login', ['email' => $Usuario->email, 'password' => self::Contrasena_Prueba])->assertRedirect('/');
        $this->get('/')->assertOk()->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina->component('Portal/Pagina_Inicio')->has('Unidades_Negocio'));
        $this->assertDatabaseHas('registros_auditoria', ['usuario_id' => $Usuario->id, 'accion' => 'inicio_sesion']);
    }

    #[Test]
    public function Usuario_Inactivo_No_Ingresa_Y_Se_Le_Cierra_La_Sesion(): void
    {
        $Usuario = $this->Crear_Cliente();
        $Usuario->update(['esta_activo' => false]);
        $this->post('/login', ['email' => $Usuario->email, 'password' => self::Contrasena_Prueba])->assertSessionHasErrors('email');
        $this->actingAs($Usuario)->get('/')->assertRedirect('/login');
    }

    #[Test]
    public function Cliente_No_Ve_Proyecto_De_Otra_Institucion(): void
    {
        $Usuario = $this->Crear_Cliente();
        $Proyecto_Ajeno = Modelo_Proyecto::create(['institucion_id' => $this->Crear_Institucion()->id, 'nombre' => 'Ajeno']);
        $this->actingAs($Usuario)->get("/proyectos/{$Proyecto_Ajeno->id}")->assertNotFound();
    }

    #[Test]
    public function Research_Requiere_Unidad_Contratada(): void
    {
        $Sin_Research = $this->Crear_Cliente($this->Crear_Institucion(['BCS']));
        $this->actingAs($Sin_Research)->get('/research')->assertRedirect('/');

        $Con_Research = $this->Crear_Cliente($this->Crear_Institucion(['RESEARCH']));
        $this->actingAs($Con_Research)->get('/research')->assertOk();
    }

    #[Test]
    public function Cliente_No_Abre_El_Cms_Y_Staff_Si(): void
    {
        $this->actingAs($this->Crear_Cliente())->get('/admin')->assertForbidden();
        $Staff = $this->Crear_Staff();
        $this->actingAs($Staff)->get('/admin')->assertOk();
        $this->actingAs($Staff)->get('/')->assertRedirect('/admin');
    }

    #[Test]
    public function Todas_Las_Paginas_Del_Portal_Cargan(): void
    {
        $Usuario = $this->Crear_Cliente($this->Crear_Institucion(['BCS', 'RESEARCH']), Modelo_Usuario::Rol_Administrador_Institucion);
        foreach (['/', '/proyectos', '/entregables', '/research', '/academia', '/facturacion', '/bolsa-de-horas', '/credencial', '/perfil'] as $Url) {
            $this->actingAs($Usuario)->get($Url)->assertOk();
        }
    }

    #[Test]
    public function Todas_Las_Paginas_Del_Cms_Cargan(): void
    {
        $Staff = $this->Crear_Staff();
        foreach (['/admin/solicitudes-horas', '/admin/auditoria', '/admin/instituciones', '/admin/usuarios/crear', "/admin/usuarios/{$Staff->id}/editar"] as $Url) {
            $this->actingAs($Staff)->get($Url)->assertOk();
        }
    }
}
