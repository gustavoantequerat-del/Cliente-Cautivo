<?php

namespace Database\Seeders;

use App\Modelos\Modelo_Contrato;
use App\Modelos\Modelo_Entregable;
use App\Modelos\Modelo_Factura;
use App\Modelos\Modelo_Institucion;
use App\Modelos\Modelo_Movimiento_Horas;
use App\Modelos\Modelo_Proyecto;
use App\Modelos\Modelo_Reporte_Research;
use App\Modelos\Modelo_Unidad_Negocio;
use App\Modelos\Modelo_Usuario;
use App\Modelos\Modelo_Video;
use Illuminate\Database\Seeder;

/**
 * Datos de demostración (cuenta ilustrativa "Banco Demo S.A."). Solo para local:
 *   php artisan migrate:fresh --seed --seeder=Sembrador_Datos_Demo
 * Contraseñas demo: ver README.
 */
class Sembrador_Datos_Demo extends Seeder
{
    public function run(): void
    {
        $this->call(Sembrador_Unidades_Negocio::class);

        $Administrador = Modelo_Usuario::create([
            'nombre' => 'Administrador Blockfinity', 'email' => 'admin@blockfinity.test',
            'password' => 'Blockfinity2026', 'rol' => Modelo_Usuario::Rol_Staff, 'cargo' => 'Staff',
        ]);
        $Ejecutiva = Modelo_Usuario::create([
            'nombre' => 'Alejandra Antezana', 'email' => 'ejecutiva@blockfinity.test',
            'password' => 'Blockfinity2026', 'rol' => Modelo_Usuario::Rol_Ejecutivo_Cuenta, 'cargo' => 'Ejecutiva de cuenta',
            'telefono' => '+59170000000', 'enlace_agenda' => 'https://calendly.com/',
        ]);

        $Banco = Modelo_Institucion::create([
            'nombre' => 'Banco Demo S.A.', 'identificador' => 'banco-demo', 'razon_social' => 'Banco Demo Sociedad Anónima',
            'nit' => '1020304050', 'correo_facturacion' => 'facturacion@bancodemo.test',
            'direccion_facturacion' => 'Av. Arce 1234, La Paz', 'puntos_alianza' => 720,
            'ejecutivo_cuenta_id' => $Ejecutiva->id,
        ]);
        $Ids_Unidades = Modelo_Unidad_Negocio::pluck('id', 'codigo');
        $Banco->Unidades_Contratadas()->attach([
            $Ids_Unidades['BCS'] => ['activa_desde' => '2026-03-01', 'nota' => 'Activa desde marzo 2026'],
            $Ids_Unidades['NEXUM'] => ['activa_desde' => '2026-01-15', 'nota' => 'Activa · 50 licencias'],
            $Ids_Unidades['RESEARCH'] => ['activa_desde' => '2026-01-01', 'nota' => 'Activa · suscripción anual'],
        ]);

        Modelo_Usuario::create([
            'nombre' => 'María Rojas', 'email' => 'maria.rojas@bancodemo.test', 'password' => 'Cliente2026demo',
            'rol' => Modelo_Usuario::Rol_Cliente_Institucional, 'institucion_id' => $Banco->id,
            'area' => 'Gerencia de Riesgos', 'cargo' => 'Gerente de Riesgos',
        ]);
        $Jorge = Modelo_Usuario::create([
            'nombre' => 'Jorge Quiroga', 'email' => 'jorge.quiroga@bancodemo.test', 'password' => 'Cliente2026demo',
            'rol' => Modelo_Usuario::Rol_Cliente_Basico, 'institucion_id' => $Banco->id, 'area' => 'Cumplimiento',
        ]);

        // --- Proyecto activo BCS -------------------------------------------
        $Proyecto_Crystal = Modelo_Proyecto::create([
            'institucion_id' => $Banco->id, 'unidad_negocio_id' => $Ids_Unidades['BCS'],
            'nombre' => 'Implementación de Crystal Expert para el monitoreo de activos virtuales',
            'descripcion' => 'Diagnóstico, configuración, capacitación y operación asistida de Crystal Expert en la Gerencia de Riesgos y Cumplimiento.',
            'avance' => 68, 'proximo_hito' => 'Comité de seguimiento · 30 oct',
            'fecha_inicio' => '2026-03-01', 'fecha_fin' => '2026-12-15',
        ]);
        // Jorge (cliente básico) participa en este proyecto; Lucía todavía no
        $Proyecto_Crystal->Participantes()->attach($Jorge->id);
        Modelo_Usuario::create([
            'nombre' => 'Lucía Fernández', 'email' => 'lucia.fernandez@bancodemo.test', 'password' => 'Cliente2026demo',
            'rol' => Modelo_Usuario::Rol_Cliente_Basico, 'institucion_id' => $Banco->id, 'area' => 'Operaciones',
        ]);
        foreach ([['Diagnóstico', 'completada'], ['Configuración', 'completada'], ['Capacitación y piloto', 'en_curso'], ['Operación asistida', 'pendiente']] as $Posicion => [$Nombre, $Estado]) {
            $Proyecto_Crystal->Fases()->create(['posicion' => $Posicion + 1, 'nombre' => $Nombre, 'estado' => $Estado]);
        }
        foreach ([
            ['2026-10-20', 'Se envió el Informe de reglas de riesgo v2 para aprobación.'],
            ['2026-10-02', 'Se habilitaron 3 usuarios adicionales en Crystal Expert.'],
            ['2026-09-18', 'Taller de monitoreo on-chain con 18 asistentes de Riesgos.'],
        ] as [$Fecha, $Detalle]) {
            $Proyecto_Crystal->Bitacora()->create(['fecha' => $Fecha, 'detalle' => $Detalle, 'usuario_id' => $Ejecutiva->id]);
        }
        $Entregables_Crystal = [
            ['Diagnóstico de brechas en monitoreo de activos virtuales', 'F1', 'aprobado'],
            ['Matriz de riesgo de contrapartes VASP', 'F1', 'aprobado'],
            ['Configuración de Crystal Expert y perfiles de usuario', 'F2', 'aprobado'],
            ['Manual de procedimientos de gestión de alertas', 'F2', 'aprobado'],
            ['Informe de reglas de riesgo v2', 'F3', 'pendiente_aprobacion'],
            ['Informe de cierre del piloto', 'F3', 'borrador'],
        ];
        foreach ($Entregables_Crystal as [$Titulo, $Fase, $Estado]) {
            Modelo_Entregable::create([
                'institucion_id' => $Banco->id, 'proyecto_id' => $Proyecto_Crystal->id, 'unidad_negocio_id' => $Ids_Unidades['BCS'],
                'titulo' => $Titulo, 'tipo' => 'informe', 'version' => $Estado === 'pendiente_aprobacion' ? '2.1' : '1.0',
                'etiqueta_fase' => $Fase, 'estado' => $Estado,
                'codigo_verificacion' => $Estado === 'borrador' ? null : hash('sha256', $Titulo),
                'enlace_externo' => $Estado === 'borrador' ? null : 'https://drive.google.com/',
                'fecha_publicacion' => now()->subDays(rand(5, 120)),
            ]);
        }

        $Proyecto_Nexum = Modelo_Proyecto::create([
            'institucion_id' => $Banco->id, 'unidad_negocio_id' => $Ids_Unidades['NEXUM'],
            'nombre' => 'Programa de formación NEXUM 2026', 'avance' => 80,
            'proximo_hito' => 'Taller Mandos Medios · 28 oct',
        ]);
        foreach ([['Diagnóstico de necesidades', 'completada'], ['Módulos virtuales', 'en_curso'], ['Talleres presenciales', 'pendiente']] as $Posicion => [$Nombre, $Estado]) {
            $Proyecto_Nexum->Fases()->create(['posicion' => $Posicion + 1, 'nombre' => $Nombre, 'estado' => $Estado]);
        }
        Modelo_Entregable::create([
            'institucion_id' => $Banco->id, 'proyecto_id' => $Proyecto_Nexum->id, 'unidad_negocio_id' => $Ids_Unidades['NEXUM'],
            'titulo' => 'Activos virtuales: riesgos, oportunidades y gobierno corporativo (Directorio)',
            'tipo' => 'presentacion', 'version' => '1.0', 'estado' => 'publicado',
            'codigo_verificacion' => hash('sha256', 'presentacion-directorio'), 'enlace_externo' => 'https://drive.google.com/',
            'fecha_publicacion' => '2026-09-12',
        ]);

        // --- Research --------------------------------------------------------
        Modelo_Reporte_Research::create([
            'titulo' => 'Pulse P2P Bolivia · T3 2026', 'tema' => 'Mercado P2P', 'autor' => 'Yhovanna Machaca',
            'resumen' => 'Dinámica del mercado P2P de stablecoins en Bolivia, señales de riesgo y lectura regulatoria.',
            'minutos_lectura' => 18, 'es_destacado' => true, 'fecha_publicacion' => '2026-10-15',
            'enlace_externo' => 'https://drive.google.com/',
        ]);
        foreach ([
            ['Exposición de su cartera a exchanges P2P', 'Mercado P2P', '2026-09-10', $Banco->id],
            ['Tipologías de riesgo en operaciones P2P', 'Mercado P2P', '2026-08-05', null],
            ['Mapa regulatorio de activos virtuales en Latinoamérica 2026', 'Regulación', '2026-10-01', null],
            ['Travel Rule: guía de implementación para entidades financieras', 'Regulación', '2026-07-12', null],
            ['Stablecoins y pagos transfronterizos en Bolivia', 'Stablecoins', '2026-06-20', null],
            ['Adopción institucional de activos digitales en la banca boliviana', 'Adopción institucional', '2026-05-08', null],
        ] as [$Titulo, $Tema, $Fecha, $Id_Institucion]) {
            Modelo_Reporte_Research::create([
                'titulo' => $Titulo, 'tema' => $Tema, 'fecha_publicacion' => $Fecha, 'institucion_id' => $Id_Institucion,
                'autor' => 'Research & Market Intelligence', 'minutos_lectura' => rand(8, 25),
                'enlace_externo' => 'https://drive.google.com/',
            ]);
        }

        // --- Academia (los enlaces reales se cargan desde el CMS) --------------
        Modelo_Video::create([
            'formato' => 'en_vivo', 'titulo' => 'Travel Rule en Bolivia: qué deben preparar las entidades financieras',
            'descripcion' => 'Lectura comparada del estándar internacional y su aplicación local, con un caso práctico.',
            'tema' => 'Normativa', 'expositor' => 'Diego Ballón Ossio · Yhovanna Machaca', 'duracion_minutos' => 60,
            'fecha_programada' => now()->addDays(10)->setTime(10, 0), 'enlace_sala' => 'https://meet.google.com/', 'puntos' => 40,
        ]);
        foreach ([
            ['grabado', 'El Pulso Oculto del Mercado Cripto Boliviano', 'Metodologías', 'Webinar CABLOCK · Blockfinity × Crystal Intelligence', 62, 20],
            ['grabado', 'Activos virtuales: lectura comparada Bolivia – Reino Unido', 'Normativa', 'Diego Ballón Ossio', 48, 20],
            ['grabado', 'Evaluación de riesgo de contrapartes VASP paso a paso', 'Metodologías', 'Equipo BCS', 40, 20],
            ['pildora', 'Cómo leer una alerta de Crystal Expert', 'Herramientas', 'Equipo BCS', 8, 10],
            ['pildora', 'Travel Rule en 7 minutos', 'Normativa', 'Research & Market Intelligence', 7, 10],
            ['pildora', 'Debida diligencia de un PSAV en 5 pasos', 'Metodologías', 'Equipo BCS', 6, 10],
        ] as [$Formato, $Titulo, $Tema, $Expositor, $Minutos, $Puntos]) {
            Modelo_Video::create([
                'formato' => $Formato, 'titulo' => $Titulo, 'tema' => $Tema, 'expositor' => $Expositor,
                'duracion_minutos' => $Minutos, 'puntos' => $Puntos,
            ]);
        }

        // --- Facturación ---------------------------------------------------------
        foreach ([
            ['F-001245', 'Suscripción Research & Market Intelligence 2026', '2026-01-05', '2026-01-20', 36000, 'pagada'],
            ['F-001388', 'Implementación Crystal Expert · Fase 1 y 2', '2026-06-10', '2026-06-25', 84000, 'pagada'],
            ['F-001502', 'Programa NEXUM 2026 · 50 licencias', '2026-09-01', '2026-09-30', 55000, 'pagada'],
            ['F-001577', 'Implementación Crystal Expert · Fase 3', '2026-10-15', '2026-11-15', 42000, 'pendiente'],
        ] as [$Numero, $Concepto, $Fecha_Emision, $Fecha_Vencimiento, $Monto, $Estado]) {
            Modelo_Factura::create([
                'institucion_id' => $Banco->id, 'numero' => $Numero, 'concepto' => $Concepto, 'fecha_emision' => $Fecha_Emision,
                'fecha_vencimiento' => $Fecha_Vencimiento, 'monto' => $Monto, 'estado' => $Estado,
                'fecha_pago' => $Estado === 'pagada' ? $Fecha_Vencimiento : null,
            ]);
        }
        Modelo_Contrato::create([
            'institucion_id' => $Banco->id, 'unidad_negocio_id' => $Ids_Unidades['BCS'], 'tipo' => 'contrato',
            'titulo' => 'Contrato de servicios BCS · Crystal Expert', 'fecha_firma' => '2026-02-20', 'fecha_vencimiento' => '2027-02-20',
            'monto' => 168000, 'codigo_verificacion' => hash('sha256', 'contrato-bcs'), 'enlace_externo' => 'https://drive.google.com/',
        ]);
        Modelo_Contrato::create([
            'institucion_id' => $Banco->id, 'unidad_negocio_id' => $Ids_Unidades['RESEARCH'], 'tipo' => 'presupuesto',
            'titulo' => 'Presupuesto Suscripción Research 2026', 'fecha_firma' => '2025-12-18', 'fecha_vencimiento' => '2026-12-31',
            'monto' => 36000,
        ]);

        // --- Bolsa de horas ------------------------------------------------------
        foreach ([
            [10, 'Bolsa incluida en contrato BCS', '2027-03-31', $Administrador->id],
            [6, 'Bolsa incluida en suscripción Research', '2026-12-31', $Administrador->id],
            [-2, 'Consulta · Regulación internacional', null, $Ejecutiva->id],
            [-1.5, 'Emergencia · Cumplimiento y antilavado', null, $Ejecutiva->id],
        ] as [$Horas, $Descripcion, $Fecha_Vencimiento, $Creado_Por]) {
            Modelo_Movimiento_Horas::create([
                'institucion_id' => $Banco->id, 'horas' => $Horas, 'descripcion' => $Descripcion,
                'fecha_vencimiento' => $Fecha_Vencimiento, 'creado_por' => $Creado_Por,
            ]);
        }
    }
}
