<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';
import Componente_Numero_Animado from '../../Componentes/Componente_Numero_Animado.vue';
import Componente_Estado_Vacio from '../../Componentes/Componente_Estado_Vacio.vue';

const Propiedades = defineProps({
    Fecha_Hoy: String, Proyectos_Activos: Array, Cantidad_Aprobaciones_Pendientes: Number, Reporte_Destacado: Object,
    Proximo_Webinar: Object, Facturas_Pendientes: Object, Saldo_Horas: Number, Nivel_Alianza: Object, Unidades_Negocio: Array,
});
const Pagina = usePage();
const Primer_Nombre = computed(() => Pagina.props.Usuario_Actual.Nombre.split(' ')[0]);
const Ejecutivo_Cuenta = computed(() => Pagina.props.Ejecutivo_Cuenta);
const Saludo = computed(() => {
    const Hora = new Date().getHours();
    return Hora < 12 ? 'Buenos días' : Hora < 19 ? 'Buenas tardes' : 'Buenas noches';
});
const Porcentaje_Nivel = computed(() => {
    const Nivel_Siguiente = Propiedades.Nivel_Alianza.Nivel_Siguiente;
    if (!Nivel_Siguiente) return 100;
    return Math.min(100, Math.round((Propiedades.Nivel_Alianza.Puntos / Nivel_Siguiente.Puntos_Minimos) * 100));
});
const Unidades_Contratadas = computed(() => Propiedades.Unidades_Negocio.filter((Unidad) => Unidad.Esta_Contratada));
const Formatear_Moneda = (Moneda) => (Moneda === 'BOB' ? 'Bs' : Moneda);
const Seccion_Por_Unidad = { BCS: '/proyectos', NEXUM: '/academia', RESEARCH: '/research' };
const Icono_Por_Unidad = { BCS: 'escudo', NEXUM: 'reproducir', RESEARCH: 'grafico', LABS: 'estrella', BUILD: 'capas', W3M: 'correo' };
</script>

<template>
    <Head title="Inicio" />

    <section :class="$style.hero">
        <div :class="$style.heroText">
            <span :class="$style.date">{{ Fecha_Hoy }}</span>
            <h1 :class="$style.title">{{ Saludo }}, {{ Primer_Nombre }} 👋</h1>
            <div class="row">
                <span :class="$style.pill"><Componente_Icono Nombre="proyectos" :Tamano="16" />{{ Proyectos_Activos.length }} {{ Proyectos_Activos.length === 1 ? 'proyecto activo' : 'proyectos activos' }}</span>
                <Link v-if="Cantidad_Aprobaciones_Pendientes" href="/entregables" :class="[$style.pill, $style.pillAlert]">
                    <span :class="$style.pulse" />{{ Cantidad_Aprobaciones_Pendientes }} por aprobar
                </Link>
            </div>
        </div>
        <span :class="$style.heroArt" aria-hidden="true"><Componente_Icono Nombre="capas" :Tamano="140" :Grosor="0.8" /></span>
    </section>

    <section :class="$style.tiles" class="escalonado" aria-label="Resumen">
        <Link href="/credencial" :class="[$style.tile, $style.tileDark]" class="elevable">
            <div class="row between">
                <span class="burbuja burbuja-oscura"><Componente_Icono Nombre="estrella" /></span>
                <span :class="$style.onDarkMuted"><Componente_Numero_Animado :Valor="Nivel_Alianza.Puntos" /> pts</span>
            </div>
            <span :class="$style.label">Nivel de alianza</span>
            <strong :class="$style.big">{{ Nivel_Alianza.Nivel_Actual.Nombre }}</strong>
            <div class="barra" :class="$style.barDark"><span :style="{ width: Porcentaje_Nivel + '%' }" /></div>
            <span :class="$style.onDarkMuted">{{ Nivel_Alianza.Nivel_Siguiente ? `Próximo: ${Nivel_Alianza.Nivel_Siguiente.Nombre}` : 'Nivel máximo 🎉' }}</span>
        </Link>
        <Link href="/bolsa-de-horas" :class="$style.tile" class="elevable">
            <span class="burbuja burbuja-verde"><Componente_Icono Nombre="reloj" /></span>
            <span :class="$style.label">Bolsa de horas</span>
            <strong :class="$style.big"><Componente_Numero_Animado :Valor="Saldo_Horas" :Decimales="2" /> h</strong>
            <span class="link-arrow">Canjear <Componente_Icono Nombre="flecha" :Tamano="16" /></span>
        </Link>
        <Link v-if="Proximo_Webinar" :href="`/academia/${Proximo_Webinar.Id}`" :class="$style.tile" class="elevable">
            <span class="burbuja burbuja-roja"><Componente_Icono Nombre="reproducir" /></span>
            <span :class="$style.label">En vivo · {{ Proximo_Webinar.Fecha }}</span>
            <strong :class="$style.mid">{{ Proximo_Webinar.Titulo }}</strong>
            <span class="link-arrow">Reservar <Componente_Icono Nombre="flecha" :Tamano="16" /></span>
        </Link>
        <Link href="/facturacion" :class="$style.tile" class="elevable">
            <span class="burbuja burbuja-ambar"><Componente_Icono Nombre="facturacion" /></span>
            <span :class="$style.label">Facturación</span>
            <strong :class="$style.mid">{{ Facturas_Pendientes.Cantidad ? `${Facturas_Pendientes.Cantidad} pendiente(s)` : 'Todo al día ✓' }}</strong>
            <span v-if="Facturas_Pendientes.Proxima" class="muted" :class="$style.small">
                {{ Formatear_Moneda(Facturas_Pendientes.Proxima.Moneda) }} {{ Facturas_Pendientes.Proxima.Monto }} · vence {{ Facturas_Pendientes.Proxima.Fecha_Vencimiento }}
            </span>
        </Link>
    </section>

    <section class="grid-2">
        <div class="card stack">
            <div class="row between">
                <h2>Proyectos en curso</h2>
                <Link href="/proyectos" class="link-arrow">Ver todos <Componente_Icono Nombre="flecha" :Tamano="16" /></Link>
            </div>
            <Componente_Estado_Vacio v-if="!Proyectos_Activos.length" Icono="proyectos" Texto="Sin proyectos activos por ahora" />
            <div class="stack escalonado" :class="$style.projects">
                <Link v-for="Proyecto in Proyectos_Activos" :key="Proyecto.Id" :href="`/proyectos/${Proyecto.Id}`" :class="$style.project" class="elevable">
                    <div class="row between">
                        <span class="badge badge-navy">{{ Proyecto.Codigo_Unidad }}</span>
                        <strong :class="$style.percent">{{ Proyecto.Avance }}%</strong>
                    </div>
                    <strong>{{ Proyecto.Nombre }}</strong>
                    <div class="barra"><span :style="{ width: Proyecto.Avance + '%' }" /></div>
                    <span v-if="Proyecto.Fase_Actual" class="muted" :class="$style.small"><Componente_Icono Nombre="lista" :Tamano="14" /> {{ Proyecto.Fase_Actual }}</span>
                </Link>
            </div>
        </div>
        <div v-if="Reporte_Destacado" class="card stack" :class="$style.reportCard">
            <div class="row">
                <span class="burbuja"><Componente_Icono Nombre="grafico" /></span>
                <span class="badge badge-navy">Nuevo · Research</span>
            </div>
            <h2>{{ Reporte_Destacado.Titulo }}</h2>
            <p :class="$style.summary">{{ Reporte_Destacado.Resumen }}</p>
            <span v-if="Reporte_Destacado.Minutos_Lectura" class="muted" :class="$style.small"><Componente_Icono Nombre="reloj" :Tamano="14" /> {{ Reporte_Destacado.Minutos_Lectura }} min de lectura</span>
            <a :href="`/research/${Reporte_Destacado.Id}/abrir`" target="_blank" rel="noopener" class="btn btn-primary" :class="$style.self">
                Abrir reporte <Componente_Icono Nombre="externo" :Tamano="16" />
            </a>
        </div>
    </section>

    <section class="stack">
        <div class="row between">
            <h2>Su ecosistema Blockfinity</h2>
            <span class="badge badge-outline">{{ Unidades_Contratadas.length }} de {{ Unidades_Negocio.length }} activas</span>
        </div>
        <div class="grid-3 escalonado">
            <article v-for="Unidad in Unidades_Negocio" :key="Unidad.Codigo" :class="[$style.unit, !Unidad.Esta_Contratada && $style.unitOff]" class="elevable" :title="Unidad.Descripcion">
                <div class="row between">
                    <span class="burbuja burbuja-sm" :class="!Unidad.Esta_Contratada && $style.bubbleOff"><Componente_Icono :Nombre="Icono_Por_Unidad[Unidad.Codigo] ?? 'capas'" :Tamano="18" /></span>
                    <span :class="['badge', Unidad.Esta_Contratada ? 'badge-ok' : 'badge-outline']">{{ Unidad.Esta_Contratada ? 'Activa' : 'Disponible' }}</span>
                </div>
                <div>
                    <span class="mono" :class="$style.code">{{ Unidad.Codigo }}</span>
                    <h3>{{ Unidad.Nombre }}</h3>
                </div>
                <span v-if="Unidad.Nota" class="muted" :class="$style.small">{{ Unidad.Nota }}</span>
                <Link v-if="Unidad.Esta_Contratada && Seccion_Por_Unidad[Unidad.Codigo]" :href="Seccion_Por_Unidad[Unidad.Codigo]" class="link-arrow">Ir <Componente_Icono Nombre="flecha" :Tamano="16" /></Link>
                <a v-else-if="!Unidad.Esta_Contratada && Ejecutivo_Cuenta" :href="`mailto:${Ejecutivo_Cuenta.Email}?subject=${encodeURIComponent('Me interesa ' + Unidad.Nombre)}`" class="link-arrow">Me interesa <Componente_Icono Nombre="flecha" :Tamano="16" /></a>
            </article>
        </div>
    </section>
</template>

<style module>
.hero {
  position: relative; overflow: hidden; border-radius: 24px; padding: 32px 36px; color: #fff;
  background: var(--gradient-hero); box-shadow: 0 18px 40px rgba(15, 34, 64, 0.22);
}
.heroText { position: relative; display: flex; flex-direction: column; gap: 14px; }
.heroArt { position: absolute; right: 24px; top: 50%; transform: translateY(-50%); color: rgba(255, 255, 255, 0.10); animation: flotar 8s ease-in-out infinite; }
.date { font-size: 13px; font-weight: 600; color: var(--side-text); }
.title { font-size: 34px; color: #fff; }
.pill {
  display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 999px; font-size: 13px; font-weight: 700;
  background: rgba(255, 255, 255, 0.12); color: #fff; text-decoration: none; transition: background 0.2s;
}
.pillAlert { background: #fff; color: var(--navy-950); }
.pillAlert:hover { background: #FFE9E6; color: var(--navy-950); }
.pulse { width: 8px; height: 8px; border-radius: 50%; background: #FF6B5E; animation: latido 1.8s infinite; }
.tiles { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 230px), 1fr)); }
.tile {
  background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 20px; box-shadow: var(--shadow);
  display: flex; flex-direction: column; gap: 8px; text-decoration: none; color: var(--ink);
}
.tile:hover { color: var(--ink); }
.tileDark { background: var(--gradient-primary); border-color: transparent; color: #fff; }
.tileDark:hover { color: #fff; }
.label { font-size: 12px; font-weight: 700; color: var(--muted); }
.tileDark .label { color: var(--side-label); }
.onDarkMuted { font-size: 13px; color: var(--side-text); }
.barDark { background: rgba(255, 255, 255, 0.15); }
.barDark > span { background: linear-gradient(90deg, #8FC7F0, #fff); }
.big { font-size: 26px; font-weight: 800; line-height: 1.1; }
.mid { font-size: 16px; font-weight: 800; line-height: 1.35; color: var(--navy-950); }
.small { font-size: 13px; display: inline-flex; align-items: center; gap: 4px; }
.projects { gap: 12px; }
.project {
  display: flex; flex-direction: column; gap: 10px; padding: 16px; border-radius: var(--radius); border: 1px solid var(--line);
  text-decoration: none; color: var(--ink); background: var(--surface);
}
.project:hover { color: var(--ink); }
.percent { font-size: 15px; color: var(--navy-700); }
.reportCard { background: linear-gradient(160deg, var(--blue-50) 0%, var(--blue-100) 100%); border-color: transparent; }
.summary { font-size: 14px; color: var(--text-2); line-height: 1.55; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
.self { align-self: flex-start; }
.unit { background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 18px; display: flex; flex-direction: column; gap: 10px; box-shadow: var(--shadow); }
.unitOff { background: var(--surface-2); border-style: dashed; box-shadow: none; }
.bubbleOff { background: var(--line); color: var(--muted); }
.code { font-size: 11px; font-weight: 600; color: var(--navy-500); }
@media (max-width: 700px) { .hero { padding: 24px 20px; } .title { font-size: 26px; } .heroArt { display: none; } }
</style>
