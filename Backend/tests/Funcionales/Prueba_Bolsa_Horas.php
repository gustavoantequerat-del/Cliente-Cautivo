<?php

namespace Tests\Funcionales;

use App\Modelos\Modelo_Movimiento_Horas;
use App\Modelos\Modelo_Solicitud_Horas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Caso_Prueba_Base;

class Prueba_Bolsa_Horas extends Caso_Prueba_Base
{
    use RefreshDatabase;

    private array $Datos_Solicitud = [
        'modalidad' => 'consulta', 'especialidad' => 'Mercado y datos', 'horas_estimadas' => 2,
        'descripcion' => 'Necesitamos una revisión de los umbrales del piloto.',
    ];

    #[Test]
    public function La_Solicitud_No_Puede_Superar_El_Saldo(): void
    {
        $Usuario = $this->Crear_Cliente();
        Modelo_Movimiento_Horas::create(['institucion_id' => $Usuario->institucion_id, 'horas' => 1, 'descripcion' => 'Bolsa']);
        $this->actingAs($Usuario)->post('/bolsa-de-horas', $this->Datos_Solicitud)->assertSessionHasErrors('horas_estimadas');
    }

    #[Test]
    public function Atender_La_Solicitud_Descuenta_Las_Horas_Una_Sola_Vez(): void
    {
        $Usuario = $this->Crear_Cliente();
        Modelo_Movimiento_Horas::create(['institucion_id' => $Usuario->institucion_id, 'horas' => 10, 'descripcion' => 'Bolsa']);
        $this->actingAs($Usuario)->post('/bolsa-de-horas', $this->Datos_Solicitud)->assertSessionHas('Mensaje_Exito');
        $Solicitud = Modelo_Solicitud_Horas::firstOrFail();

        $Superadmin = $this->Crear_Superadmin();
        $Datos_Atendida = ['estado' => 'atendida', 'horas_cobradas' => 1.5, 'respuesta' => 'Atendido'];
        $this->actingAs($Superadmin)->put("/admin/solicitudes-horas/{$Solicitud->id}", $Datos_Atendida)->assertSessionHas('Mensaje_Exito');
        $this->actingAs($Superadmin)->put("/admin/solicitudes-horas/{$Solicitud->id}", $Datos_Atendida);

        $this->assertSame(8.5, $Usuario->Institucion->fresh()->Calcular_Saldo_Horas());
    }
}
