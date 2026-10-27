<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use App\Models\Contract;
use App\Models\Deliverable;
use App\Models\HourMovement;
use App\Models\Institution;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ResearchReport;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Seeder;

/**
 * Datos de demostración (cuenta ilustrativa "Banco Demo S.A.").
 * Contraseñas demo: ver README. Cámbielas antes de producción.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(BusinessUnitSeeder::class);

        $admin = User::create([
            'name' => 'Administrador Blockfinity', 'email' => 'admin@blockfinity.test',
            'password' => 'Blockfinity2026', 'role' => 'admin', 'position' => 'Staff',
        ]);
        $manager = User::create([
            'name' => 'Alejandra Antezana', 'email' => 'ejecutiva@blockfinity.test',
            'password' => 'Blockfinity2026', 'role' => 'admin', 'position' => 'Client Success',
            'phone' => '+591 700 00000', 'booking_url' => 'https://calendly.com/',
        ]);

        $bank = Institution::create([
            'name' => 'Banco Demo S.A.', 'slug' => 'banco-demo', 'legal_name' => 'Banco Demo Sociedad Anónima',
            'tax_id' => '1020304050', 'billing_email' => 'facturacion@bancodemo.test',
            'billing_address' => 'Av. Arce 1234, La Paz', 'alliance_points' => 720,
            'account_manager_id' => $manager->id,
        ]);
        $units = BusinessUnit::pluck('id', 'code');
        $bank->units()->attach([
            $units['BCS'] => ['active_since' => '2026-03-01', 'note' => 'Activa desde marzo 2026'],
            $units['NEXUM'] => ['active_since' => '2026-01-15', 'note' => 'Activa · 50 licencias'],
            $units['RESEARCH'] => ['active_since' => '2026-01-01', 'note' => 'Activa · suscripción anual'],
        ]);

        $maria = User::create([
            'name' => 'María Rojas', 'email' => 'maria.rojas@bancodemo.test', 'password' => 'Cliente2026demo',
            'role' => 'client_admin', 'institution_id' => $bank->id, 'area' => 'Gerencia de Riesgos',
            'position' => 'Gerente de Riesgos',
        ]);
        User::create([
            'name' => 'Jorge Quiroga', 'email' => 'jorge.quiroga@bancodemo.test', 'password' => 'Cliente2026demo',
            'role' => 'client', 'institution_id' => $bank->id, 'area' => 'Cumplimiento',
        ]);

        // --- Proyecto activo BCS -------------------------------------------
        $crystal = Project::create([
            'institution_id' => $bank->id, 'business_unit_id' => $units['BCS'],
            'name' => 'Implementación de Crystal Expert para el monitoreo de activos virtuales',
            'description' => 'Diagnóstico, configuración, capacitación y operación asistida de Crystal Expert en la Gerencia de Riesgos y Cumplimiento.',
            'progress' => 68, 'next_milestone' => 'Comité de seguimiento · 30 oct',
            'starts_on' => '2026-03-01', 'ends_on' => '2026-12-15',
        ]);
        foreach ([['Diagnóstico', 'done'], ['Configuración', 'done'], ['Capacitación y piloto', 'current'], ['Operación asistida', 'pending']] as $i => [$name, $status]) {
            $crystal->phases()->create(['position' => $i + 1, 'name' => $name, 'status' => $status]);
        }
        foreach ([
            ['2026-10-20', 'Se envió el Informe de reglas de riesgo v2 para aprobación.'],
            ['2026-10-02', 'Se habilitaron 3 usuarios adicionales en Crystal Expert.'],
            ['2026-09-18', 'Taller de monitoreo on-chain con 18 asistentes de Riesgos.'],
        ] as [$date, $body]) {
            $crystal->updates()->create(['happened_on' => $date, 'body' => $body, 'user_id' => $manager->id]);
        }
        $deliverables = [
            ['Diagnóstico de brechas en monitoreo de activos virtuales', 'F1', 'approved'],
            ['Matriz de riesgo de contrapartes VASP', 'F1', 'approved'],
            ['Configuración de Crystal Expert y perfiles de usuario', 'F2', 'approved'],
            ['Manual de procedimientos de gestión de alertas', 'F2', 'approved'],
            ['Informe de reglas de riesgo v2', 'F3', 'pending_approval'],
            ['Informe de cierre del piloto', 'F3', 'draft'],
        ];
        foreach ($deliverables as [$title, $phase, $status]) {
            Deliverable::create([
                'institution_id' => $bank->id, 'project_id' => $crystal->id, 'business_unit_id' => $units['BCS'],
                'title' => $title, 'kind' => 'informe', 'version' => $status === 'pending_approval' ? '2.1' : '1.0',
                'phase_label' => $phase, 'status' => $status, 'requires_approval' => true,
                'sha256' => $status === 'draft' ? null : hash('sha256', $title),
                'external_url' => $status === 'draft' ? null : 'https://drive.google.com/',
                'published_at' => now()->subDays(rand(5, 120)),
            ]);
        }

        $nexum = Project::create([
            'institution_id' => $bank->id, 'business_unit_id' => $units['NEXUM'],
            'name' => 'Programa de formación NEXUM 2026', 'progress' => 80,
            'next_milestone' => 'Taller Mandos Medios · 28 oct',
        ]);
        foreach ([['Diagnóstico de necesidades', 'done'], ['Módulos virtuales', 'current'], ['Talleres presenciales', 'pending']] as $i => [$name, $status]) {
            $nexum->phases()->create(['position' => $i + 1, 'name' => $name, 'status' => $status]);
        }
        Deliverable::create([
            'institution_id' => $bank->id, 'project_id' => $nexum->id, 'business_unit_id' => $units['NEXUM'],
            'title' => 'Activos virtuales: riesgos, oportunidades y gobierno corporativo (Directorio)',
            'kind' => 'presentacion', 'version' => '1.0', 'status' => 'published',
            'sha256' => hash('sha256', 'presentacion-directorio'), 'external_url' => 'https://drive.google.com/',
            'published_at' => '2026-09-12',
        ]);

        // --- Research --------------------------------------------------------
        ResearchReport::create([
            'title' => 'Pulse P2P Bolivia · T3 2026', 'topic' => 'Mercado P2P', 'author' => 'Yhovanna Machaca',
            'summary' => 'Dinámica del mercado P2P de stablecoins en Bolivia, señales de riesgo y lectura regulatoria.',
            'reading_minutes' => 18, 'is_featured' => true, 'published_on' => '2026-10-15',
            'external_url' => 'https://drive.google.com/',
        ]);
        foreach ([
            ['Exposición de su cartera a exchanges P2P', 'Mercado P2P', '2026-09-10', $bank->id],
            ['Tipologías de riesgo en operaciones P2P', 'Mercado P2P', '2026-08-05', null],
            ['Mapa regulatorio de activos virtuales en Latinoamérica 2026', 'Regulación', '2026-10-01', null],
            ['Travel Rule: guía de implementación para entidades financieras', 'Regulación', '2026-07-12', null],
            ['Stablecoins y pagos transfronterizos en Bolivia', 'Stablecoins', '2026-06-20', null],
            ['Adopción institucional de activos digitales en la banca boliviana', 'Adopción institucional', '2026-05-08', null],
        ] as [$title, $topic, $date, $inst]) {
            ResearchReport::create([
                'title' => $title, 'topic' => $topic, 'published_on' => $date, 'institution_id' => $inst,
                'author' => 'Research & Market Intelligence', 'reading_minutes' => rand(8, 25),
                'external_url' => 'https://drive.google.com/',
            ]);
        }

        // --- Academia (los enlaces reales se cargan desde el CMS) --------------
        Video::create([
            'kind' => 'live', 'title' => 'Travel Rule en Bolivia: qué deben preparar las entidades financieras',
            'description' => 'Lectura comparada del estándar internacional y su aplicación local, con un caso práctico.',
            'topic' => 'Normativa', 'speaker' => 'Diego Ballón Ossio · Yhovanna Machaca', 'duration_minutes' => 60,
            'scheduled_at' => now()->addDays(10)->setTime(10, 0), 'live_url' => 'https://meet.google.com/', 'points' => 40,
        ]);
        foreach ([
            ['recorded', 'El Pulso Oculto del Mercado Cripto Boliviano', 'Metodologías', 'Webinar CABLOCK · Blockfinity × Crystal Intelligence', 62, 20],
            ['recorded', 'Activos virtuales: lectura comparada Bolivia – Reino Unido', 'Normativa', 'Diego Ballón Ossio', 48, 20],
            ['recorded', 'Evaluación de riesgo de contrapartes VASP paso a paso', 'Metodologías', 'Equipo BCS', 40, 20],
            ['pill', 'Cómo leer una alerta de Crystal Expert', 'Herramientas', 'Equipo BCS', 8, 10],
            ['pill', 'Travel Rule en 7 minutos', 'Normativa', 'Research & Market Intelligence', 7, 10],
            ['pill', 'Debida diligencia de un PSAV en 5 pasos', 'Metodologías', 'Equipo BCS', 6, 10],
        ] as [$kind, $title, $topic, $speaker, $min, $pts]) {
            Video::create(compact('kind', 'title', 'topic', 'speaker') + ['duration_minutes' => $min, 'points' => $pts]);
        }

        // --- Facturación ---------------------------------------------------------
        foreach ([
            ['F-001245', 'Suscripción Research & Market Intelligence 2026', '2026-01-05', '2026-01-20', 36000, 'paid'],
            ['F-001388', 'Implementación Crystal Expert · Fase 1 y 2', '2026-06-10', '2026-06-25', 84000, 'paid'],
            ['F-001502', 'Programa NEXUM 2026 · 50 licencias', '2026-09-01', '2026-09-30', 55000, 'paid'],
            ['F-001577', 'Implementación Crystal Expert · Fase 3', '2026-10-15', '2026-11-15', 42000, 'pending'],
        ] as [$n, $c, $i, $d, $a, $s]) {
            Invoice::create(['institution_id' => $bank->id, 'number' => $n, 'concept' => $c, 'issued_on' => $i,
                'due_on' => $d, 'amount' => $a, 'status' => $s, 'paid_on' => $s === 'paid' ? $d : null]);
        }
        Contract::create([
            'institution_id' => $bank->id, 'business_unit_id' => $units['BCS'], 'kind' => 'contract',
            'title' => 'Contrato de servicios BCS · Crystal Expert', 'signed_on' => '2026-02-20', 'expires_on' => '2027-02-20',
            'amount' => 168000, 'sha256' => hash('sha256', 'contrato-bcs'), 'external_url' => 'https://drive.google.com/',
        ]);
        Contract::create([
            'institution_id' => $bank->id, 'business_unit_id' => $units['RESEARCH'], 'kind' => 'quote',
            'title' => 'Presupuesto Suscripción Research 2026', 'signed_on' => '2025-12-18', 'expires_on' => '2026-12-31',
            'amount' => 36000,
        ]);

        // --- Bolsa de horas ------------------------------------------------------
        HourMovement::create(['institution_id' => $bank->id, 'hours' => 10, 'description' => 'Bolsa incluida en contrato BCS',
            'expires_on' => '2027-03-31', 'created_by' => $admin->id]);
        HourMovement::create(['institution_id' => $bank->id, 'hours' => 6, 'description' => 'Bolsa incluida en suscripción Research',
            'expires_on' => '2026-12-31', 'created_by' => $admin->id]);
        HourMovement::create(['institution_id' => $bank->id, 'hours' => -2, 'description' => 'Consulta · Regulación internacional',
            'created_by' => $manager->id]);
        HourMovement::create(['institution_id' => $bank->id, 'hours' => -1.5, 'description' => 'Emergencia · Cumplimiento y antilavado',
            'created_by' => $manager->id]);
    }
}
