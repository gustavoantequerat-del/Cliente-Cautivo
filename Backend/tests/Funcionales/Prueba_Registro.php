<?php

namespace Tests\Funcionales;

use App\Modelos\Modelo_Institucion;
use App\Modelos\Modelo_Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Pagina_Inertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\Caso_Prueba_Base;

class Prueba_Registro extends Caso_Prueba_Base
{
    use RefreshDatabase;

    private function Datos_Registro(string $Nombre = 'Ana Pérez', string $Area = 'Riesgos'): array
    {
        return ['nombre' => $Nombre, 'email' => 'ana@correo.test', 'area' => $Area,
            'password' => 'Clave12345segura', 'password_confirmation' => 'Clave12345segura'];
    }

    #[Test]
    public function Cada_Institucion_Nace_Con_Su_Link_Y_Registra_Usuarios_Institucionales(): void
    {
        $Banco = $this->Crear_Institucion();
        $this->assertNotEmpty($Banco->codigo_registro);

        $this->get("/registro/{$Banco->codigo_registro}")->assertOk()->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina
            ->component('Acceso/Pagina_Registro')->where('Institucion.Nombre', $Banco->nombre));
        $this->post("/registro/{$Banco->codigo_registro}", $this->Datos_Registro())->assertRedirect('/');

        $Usuario = Modelo_Usuario::where('email', 'ana@correo.test')->firstOrFail();
        $this->assertSame(Modelo_Usuario::Rol_Usuario_Institucional, $Usuario->rol);
        $this->assertEquals($Banco->id, $Usuario->institucion_id);
        $this->assertAuthenticatedAs($Usuario);
        $this->assertNotNull($Usuario->numero_credencial);
    }

    #[Test]
    public function Una_Vez_Por_Institucion_Pero_En_Varias_Con_Datos_Separados(): void
    {
        $Bnb = $this->Crear_Institucion();
        $Otro_Banco = $this->Crear_Institucion();

        $this->post("/registro/{$Bnb->codigo_registro}", $this->Datos_Registro('Ana Pérez', 'Riesgos'))->assertRedirect('/');
        auth()->logout();
        $this->post("/registro/{$Bnb->codigo_registro}", $this->Datos_Registro())->assertSessionHasErrors('email');
        $this->post("/registro/{$Otro_Banco->codigo_registro}", $this->Datos_Registro('Ana P. Rojas', 'Cumplimiento'))->assertRedirect('/');

        $Cuentas = Modelo_Usuario::where('email', 'ana@correo.test')->orderBy('id')->get();
        $this->assertCount(2, $Cuentas);
        $this->assertSame(['Riesgos', 'Cumplimiento'], $Cuentas->pluck('area')->all());
        $this->assertNotEquals($Cuentas[0]->numero_credencial, $Cuentas[1]->numero_credencial);
    }

    #[Test]
    public function Con_Cuentas_En_Dos_Instituciones_El_Login_Pide_Elegir(): void
    {
        $Bnb = $this->Crear_Institucion();
        $Otro_Banco = $this->Crear_Institucion();
        foreach ([$Bnb, $Otro_Banco] as $Institucion) {
            $this->post("/registro/{$Institucion->codigo_registro}", $this->Datos_Registro());
            auth()->logout();
        }
        $Credenciales = ['email' => 'ana@correo.test', 'password' => 'Clave12345segura'];

        $this->post('/login', $Credenciales)->assertRedirect()->assertSessionHas('Instituciones_Para_Elegir', fn ($Lista) => count($Lista) === 2);
        $this->assertGuest();
        $this->post('/login', $Credenciales + ['institucion_id' => $Otro_Banco->id])->assertRedirect('/');
        $this->assertEquals($Otro_Banco->id, auth()->user()->institucion_id);
    }

    #[Test]
    public function El_Link_Cerrado_O_Regenerado_No_Registra(): void
    {
        $Superadmin = $this->Crear_Superadmin();
        $Banco = $this->Crear_Institucion();
        $Codigo_Anterior = $Banco->codigo_registro;
        $Datos_Banco = ['nombre' => $Banco->nombre, 'identificador' => $Banco->identificador, 'esta_activa' => 1,
            'color_primario' => '#1F3F73', 'color_secundario' => '#5AA9E6'];

        $this->actingAs($Superadmin)->get("/admin/instituciones/{$Banco->id}/editar")->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina
            ->where('Valores.codigo_registro', url("/registro/{$Codigo_Anterior}")));
        $this->actingAs($Superadmin)->post("/admin/instituciones/{$Banco->id}", $Datos_Banco + ['registro_abierto' => 1, 'codigo_registro_regenerar' => 1]);
        $Codigo_Nuevo = $Banco->fresh()->codigo_registro;
        $this->assertNotSame($Codigo_Anterior, $Codigo_Nuevo);
        auth()->logout();
        $this->post("/registro/{$Codigo_Anterior}", $this->Datos_Registro())->assertNotFound();

        $Banco->update(['registro_abierto' => false]);
        $this->get("/registro/{$Codigo_Nuevo}")->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina->where('Institucion', null));
        $this->post("/registro/{$Codigo_Nuevo}", $this->Datos_Registro())->assertNotFound();
        $this->assertSame(0, Modelo_Usuario::where('email', 'ana@correo.test')->count());
    }

    #[Test]
    public function El_Cms_Permite_El_Mismo_Correo_En_Otra_Institucion_Pero_No_En_La_Misma(): void
    {
        $Superadmin = $this->Crear_Superadmin();
        $Bnb = $this->Crear_Institucion();
        $Otro_Banco = $this->Crear_Institucion();
        $Datos = ['nombre' => 'Luis Mamani', 'email' => 'luis@correo.test', 'password' => 'Clave12345segura', 'rol' => 'usuario_institucional', 'esta_activo' => 1];

        $this->actingAs($Superadmin)->post('/admin/usuarios', $Datos + ['institucion_id' => $Bnb->id])->assertRedirect('/admin/usuarios');
        $this->actingAs($Superadmin)->post('/admin/usuarios', $Datos + ['institucion_id' => $Bnb->id])->assertSessionHasErrors('email');
        $this->actingAs($Superadmin)->post('/admin/usuarios', $Datos + ['institucion_id' => $Otro_Banco->id])->assertRedirect('/admin/usuarios');
        $this->assertSame(2, Modelo_Usuario::where('email', 'luis@correo.test')->count());
    }
}
