<?php

namespace Tests\Funcionales;

use App\Modelos\Modelo_Entregable;
use App\Modelos\Modelo_Proyecto;
use App\Modelos\Modelo_Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Pagina_Inertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\Caso_Prueba_Base;

class Prueba_Roles extends Caso_Prueba_Base
{
    use RefreshDatabase;

    #[Test]
    public function El_Cliente_Basico_No_Entra_A_Las_Secciones_Institucionales(): void
    {
        $Basico = $this->Crear_Cliente(null, Modelo_Usuario::Rol_Usuario_Institucional);
        foreach (['/facturacion', '/bolsa-de-horas', '/entregables'] as $Url) {
            $this->actingAs($Basico)->get($Url)->assertRedirect('/');
        }
        $this->actingAs($Basico)->get('/')->assertOk()->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina->where('Panel_Institucional', null));
        foreach (['/academia', '/proyectos', '/credencial', '/perfil'] as $Url) {
            $this->actingAs($Basico)->get($Url)->assertOk();
        }
    }

    #[Test]
    public function El_Cliente_Basico_Solo_Ve_Los_Proyectos_Donde_Lo_Inscribieron(): void
    {
        $Institucional = $this->Crear_Cliente();
        $Basico = $this->Crear_Cliente($Institucional->Institucion, Modelo_Usuario::Rol_Usuario_Institucional);
        $Proyecto = Modelo_Proyecto::create(['institucion_id' => $Institucional->institucion_id, 'nombre' => 'Piloto']);
        $Entregable = Modelo_Entregable::create(['institucion_id' => $Proyecto->institucion_id, 'proyecto_id' => $Proyecto->id,
            'titulo' => 'Informe', 'estado' => 'publicado', 'enlace_externo' => 'https://drive.google.com/x']);

        $this->actingAs($Basico)->get("/proyectos/{$Proyecto->id}")->assertNotFound();
        $this->actingAs($Basico)->get("/entregables/{$Entregable->id}/descargar")->assertNotFound();

        $this->actingAs($Institucional)->post("/proyectos/{$Proyecto->id}/participantes", ['usuario_id' => $Basico->id, 'inscrito' => true])
            ->assertSessionHas('Mensaje_Exito');

        $this->actingAs($Basico)->get('/proyectos')->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina->has('Lista_Proyectos', 1));
        $this->actingAs($Basico)->get("/proyectos/{$Proyecto->id}")->assertOk()->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina->where('Equipo', null));
        $this->actingAs($Basico)->get("/entregables/{$Entregable->id}/descargar")->assertRedirect('https://drive.google.com/x');
        $this->actingAs($Basico)->post("/proyectos/{$Proyecto->id}/participantes", ['usuario_id' => $Basico->id, 'inscrito' => false])->assertRedirect('/');
    }

    #[Test]
    public function El_Institucional_Solo_Inscribe_A_Clientes_Basicos_De_Su_Institucion(): void
    {
        $Institucional = $this->Crear_Cliente();
        $Proyecto = Modelo_Proyecto::create(['institucion_id' => $Institucional->institucion_id, 'nombre' => 'Piloto']);
        $Basico_Ajeno = $this->Crear_Cliente(null, Modelo_Usuario::Rol_Usuario_Institucional);

        $this->actingAs($Institucional)->post("/proyectos/{$Proyecto->id}/participantes", ['usuario_id' => $Basico_Ajeno->id, 'inscrito' => true])
            ->assertSessionHasErrors('usuario_id');
        $this->assertSame(0, $Proyecto->Participantes()->count());
    }

    #[Test]
    public function El_Ejecutivo_Solo_Gestiona_Las_Instituciones_A_Su_Cargo(): void
    {
        $Mi_Institucion = $this->Crear_Institucion();
        $Otra_Institucion = $this->Crear_Institucion();
        $Ejecutivo = $this->Crear_Usuario_Ejecutivo($Mi_Institucion);
        $Mi_Cliente = $this->Crear_Cliente($Mi_Institucion, Modelo_Usuario::Rol_Usuario_Institucional);
        $Cliente_Ajeno = $this->Crear_Cliente($Otra_Institucion, Modelo_Usuario::Rol_Usuario_Institucional);

        $this->post('/admin/login', ['email' => $Ejecutivo->email, 'password' => self::Contrasena_Prueba])->assertRedirect('/admin');

        $this->actingAs($Ejecutivo)->get('/admin/usuarios')->assertOk()
            ->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina->has('Filas.data', 1)->where('Filas.data.0.id', $Mi_Cliente->id));
        $this->actingAs($Ejecutivo)->get("/admin/usuarios/{$Cliente_Ajeno->id}/editar")->assertNotFound();

        // Contenido global y auditoría: solo el superadmin
        foreach (['/admin/videos', '/admin/auditoria', '/admin/forja'] as $Url) {
            $this->actingAs($Ejecutivo)->get($Url)->assertForbidden();
        }

        // Instituciones: ve y edita solo las suyas, no las elimina, y las que crea quedan a su cargo
        $this->actingAs($Ejecutivo)->get('/admin/instituciones')->assertOk()
            ->assertInertia(fn (Pagina_Inertia $Pagina) => $Pagina->has('Filas.data', 1)->where('Recurso.Permite_Eliminar', false));
        $this->actingAs($Ejecutivo)->get("/admin/instituciones/{$Otra_Institucion->id}/editar")->assertNotFound();
        $this->actingAs($Ejecutivo)->delete("/admin/instituciones/{$Mi_Institucion->id}")->assertForbidden();
        $this->actingAs($Ejecutivo)->post('/admin/instituciones', ['nombre' => 'Cooperativa Nueva', 'identificador' => 'coop-nueva',
            'esta_activa' => 1, 'color_primario' => '#0E6B4F', 'color_secundario' => '#F2C94C'])->assertRedirect('/admin/instituciones');
        $this->assertDatabaseHas('instituciones', ['identificador' => 'coop-nueva', 'ejecutivo_cuenta_id' => $Ejecutivo->id, 'color_primario' => '#0E6B4F']);

        // No puede crear superadmin ni clientes de otra institución
        $Datos_Nuevo = ['nombre' => 'Ana Pérez', 'email' => 'ana@prueba.test', 'password' => 'Clave12345segura', 'esta_activo' => 1];
        $this->actingAs($Ejecutivo)->post('/admin/usuarios', $Datos_Nuevo + ['rol' => 'superadmin', 'institucion_id' => $Mi_Institucion->id])
            ->assertSessionHasErrors('rol');
        $this->actingAs($Ejecutivo)->post('/admin/usuarios', $Datos_Nuevo + ['rol' => 'usuario_institucional', 'institucion_id' => $Otra_Institucion->id])
            ->assertSessionHasErrors('institucion_id');
        $this->actingAs($Ejecutivo)->post('/admin/usuarios', $Datos_Nuevo + ['rol' => 'administrador_delegado', 'institucion_id' => $Mi_Institucion->id])
            ->assertRedirect('/admin/usuarios');
        $this->assertDatabaseHas('usuarios', ['email' => 'ana@prueba.test', 'rol' => 'administrador_delegado', 'institucion_id' => $Mi_Institucion->id]);
    }

    #[Test]
    public function El_Personal_De_Blockfinity_No_Usa_El_Portal(): void
    {
        $Ejecutivo = $this->Crear_Usuario_Ejecutivo($this->Crear_Institucion());
        $this->actingAs($Ejecutivo)->get('/')->assertRedirect('/admin');
    }
}
