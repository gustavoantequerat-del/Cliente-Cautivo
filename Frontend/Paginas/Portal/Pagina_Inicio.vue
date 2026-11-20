<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';
import Componente_Numero_Animado from '../../Componentes/Componente_Numero_Animado.vue';
import Componente_Estado_Vacio from '../../Componentes/Componente_Estado_Vacio.vue';
import Componente_Avatar from '../../Componentes/Componente_Avatar.vue';

const Propiedades = defineProps({
    Fecha_Hoy: String, Proyectos_Activos: Array, Reporte_Destacado: Object, Proximo_Webinar: Object,
    Mi_Avance: Object, Nivel_Alianza: Object, Panel_Institucional: Object,
});
const Pagina = usePage();
const Primer_Nombre = computed(() => Pagina.props.Usuario_Actual.Nombre.split(' ')[0]);
const Ejecutivo_Cuenta = computed(() => Pagina.props.Ejecutivo_Cuenta);
const Saludo = computed(() => {
    const Hora = new Date().getHours();
    return Hora < 12 ? 'Buenos días' : Hora < 19 ? 'Buenas tardes' : 'Buenas noches';
});
const Porcentaje_Nivel = computed(() => Math.round((Propiedades.Nivel_Alianza.Servicios_Contratados / Math.max(1, Propiedades.Nivel_Alianza.Servicios_Totales)) * 100));
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
                <Link v-if="Panel_Institucional?.Cantidad_Aprobaciones_Pendientes" href="/entregables" :class="[$style.pill, $style.pillAlert]">
                    <span :class="$style.pulse" />{{ Panel_Institucional.Cantidad_Aprobaciones_Pendientes }} por aprobar
                </Link>
            </div>
        </div>
        <span :class="$style.heroArt" aria-hidden="true"><Componente_Icono Nombre="capas" :Tamano="140" :Grosor="0.8" /></span>
    </section>

    <section :class="$style.tiles" class="escalonado" aria-label="Resumen">
        <Link href="/credencial" :class="[$style.tile, $style.tileDark]" class="elevable">
            <span class="burbuja burbuja-oscura"><Componente_Icono Nombre="estrella" /></span>
            <span :class="$style.label">Nivel de alianza</span>
            <strong :class="$style.big">{{ Nivel_Alianza.Nivel_Actual.Nombre }}</strong>
            <div class="barra" :class="$style.barDark"><span :style="{ width: Porcentaje_Nivel + '%' }" /></div>
            <span :class="$style.onDarkMuted">{{ Nivel_Alianza.Servicios_Contratados }} de {{ Nivel_Alianza.Servicios_Totales }} servicios contratados</span>
        </Link>
        <Link href="/academia" :class="$style.tile" class="elevable">
            <span class="burbuja burbuja-violeta"><Componente_Icono Nombre="reproducir" /></span>
            <span :class="$style.label">Mi formación</span>
            <strong :class="$style.big"><Componente_Numero_Animado :Valor="Mi_Avance.Completados" /> <small :class="$style.unit">completados</small></strong>
            <span class="muted" :class="$style.small"><Componente_Numero_Animado :Valor="Mi_Avance.Horas" :Decimales="1" /> h de formación</span>
        </Link>
        <Link href="/avatar" :class="$style.tile" class="elevable">
            <span :class="$style.avatarTile"><Componente_Avatar :Objetos="$page.props.Usuario_Actual.Objetos_Avatar" Recorte="busto" /></span>
            <span :class="$style.label">Mis monedas</span>
            <strong :class="$style.big"><Componente_Numero_Animado :Valor="Mi_Avance.Monedas" /> <small :class="$style.unit">monedas</small></strong>
            <span class="link-arrow">Vestir mi avatar <Componente_Icono Nombre="flecha" :Tamano="16" /></span>
        </Link>
        <Link v-if="Proximo_Webinar" :href="`/academia/${Proximo_Webinar.Id}`" :class="$style.tile" class="elevable">
            <span class="burbuja burbuja-roja"><Componente_Icono Nombre="calendario" /></span>
            <span :class="$style.label">En vivo · {{ Proximo_Webinar.Fecha }}</span>
            <strong :class="$style.mid">{{ Proximo_Webinar.Titulo }}</strong>
        </Link>
    </section>

    <section v-if="Panel_Institucional" class="stack">
        <h2>Gestión de su institución</h2>
        <div :class="$style.manage" class="escalonado">
            <Link href="/entregables" :class="$style.manageItem" class="elevable">
                <span class="burbuja burbuja-sm"><Componente_Icono Nombre="boveda" :Tamano="18" /></span>
                <span :class="$style.manageText"><strong><Componente_Numero_Animado :Valor="Panel_Institucional.Cantidad_Aprobaciones_Pendientes" /></strong>por aprobar</span>
            </Link>
            <Link href="/facturacion" :class="$style.manageItem" class="elevable">
                <span class="burbuja burbuja-sm burbuja-ambar"><Componente_Icono Nombre="facturacion" :Tamano="18" /></span>
                <span :class="$style.manageText">
                    <strong>{{ Panel_Institucional.Facturas_Pendientes.Cantidad || '✓' }}</strong>
                    {{ Panel_Institucional.Facturas_Pendientes.Proxima ? `${Formatear_Moneda(Panel_Institucional.Facturas_Pendientes.Proxima.Moneda)} ${Panel_Institucional.Facturas_Pendientes.Proxima.Monto} por pagar` : 'facturas al día' }}
                </span>
            </Link>
            <Link href="/bolsa-de-horas" :class="$style.manageItem" class="elevable">
                <span class="burbuja burbuja-sm burbuja-verde"><Componente_Icono Nombre="reloj" :Tamano="18" /></span>
                <span :class="$style.manageText"><strong><Componente_Numero_Animado :Valor="Panel_Institucional.Saldo_Horas" :Decimales="2" /> h</strong>disponibles</span>
            </Link>
            <Link href="/proyectos" :class="$style.manageItem" class="elevable">
                <span class="burbuja burbuja-sm burbuja-violeta"><Componente_Icono Nombre="usuarios" :Tamano="18" /></span>
                <span :class="$style.manageText"><strong><Componente_Numero_Animado :Valor="Panel_Institucional.Cantidad_Equipo" /></strong>personas en su equipo</span>
            </Link>
        </div>
    </section>

    <section class="grid-2">
        <div class="card stack">
            <div class="row between">
                <h2>Mis proyectos</h2>
                <Link v-if="Proyectos_Activos.length" href="/proyectos" class="link-arrow">Ver todos <Componente_Icono Nombre="flecha" :Tamano="16" /></Link>
            </div>
            <Componente_Estado_Vacio v-if="!Proyectos_Activos.length" Icono="proyectos"
                                     :Texto="Panel_Institucional ? 'Sin proyectos activos por ahora' : 'Aún no participa en proyectos'" />
            <div v-else class="stack escalonado" :class="$style.projects">
                <Link v-for="Proyecto in Proyectos_Activos" :key="Proyecto.Id" :href="`/proyectos/${Proyecto.Id}`" :class="$style.project" class="elevable">
                    <div class="row between">
                        <span class="badge badge-navy">{{ Proyecto.Codigo_Unidad ?? 'Blockfinity' }}</span>
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
            <Link :href="`/research/${Reporte_Destacado.Id}`" class="btn btn-primary" :class="$style.self">
                Leer reporte <Componente_Icono Nombre="flecha" :Tamano="16" />
            </Link>
        </div>
    </section>

    <section v-if="Panel_Institucional" class="stack">
        <h2>Su ecosistema Blockfinity</h2>
        <div class="grid-3 escalonado">
            <article v-for="Unidad in Panel_Institucional.Unidades_Negocio" :key="Unidad.Codigo" :class="[$style.unitCard, !Unidad.Esta_Contratada && $style.unitOff]" :title="Unidad.Descripcion">
                <span class="burbuja burbuja-sm" :class="!Unidad.Esta_Contratada && $style.bubbleOff"><Componente_Icono :Nombre="Icono_Por_Unidad[Unidad.Codigo] ?? 'capas'" :Tamano="18" /></span>
                <div :class="$style.unitText">
                    <strong>{{ Unidad.Nombre }}</strong>
                    <span :class="['badge', Unidad.Esta_Contratada ? 'badge-ok' : 'badge-outline']">{{ Unidad.Esta_Contratada ? 'Activa' : 'Disponible' }}</span>
                </div>
                <Link v-if="Unidad.Esta_Contratada && Seccion_Por_Unidad[Unidad.Codigo]" :href="Seccion_Por_Unidad[Unidad.Codigo]" :class="$style.unitGo" :aria-label="`Ir a ${Unidad.Nombre}`">
                    <Componente_Icono Nombre="flecha" :Tamano="18" />
                </Link>
                <a v-else-if="!Unidad.Esta_Contratada && Ejecutivo_Cuenta" :href="`mailto:${Ejecutivo_Cuenta.Email}?subject=${encodeURIComponent('Me interesa ' + Unidad.Nombre)}`"
                   :class="$style.unitGo" :aria-label="`Me interesa ${Unidad.Nombre}`"><Componente_Icono Nombre="correo" :Tamano="18" /></a>
            </article>
        </div>
    </section>
</template>

<style module>
.hero {
  position: relative; overflow: hidden; border-radius: 24px; padding: 32px 36px; color: #fff;
  background: var(--gradient-hero); box-shadow: 0 18px 40px rgba(15, 34, 64, 0.22);
}
.heroText { position: relative; z-index: 1; display: flex; flex-direction: column; gap: 14px; }
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
.tiles { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr)); align-items: stretch; }
.tile {
  background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 20px; box-shadow: var(--shadow);
  display: flex; flex-direction: column; align-items: flex-start; gap: 8px; text-decoration: none; color: var(--ink);
}
.tile:hover { color: var(--ink); }
.tileDark { background: var(--gradient-primary); border-color: transparent; color: #fff; }
.tileDark:hover { color: #fff; }
.tileDark .barra { align-self: stretch; }
.label { font-size: 12px; font-weight: 700; color: var(--muted); }
.tileDark .label { color: var(--side-label); }
.onDarkMuted { font-size: 13px; color: var(--side-text); }
.barDark { background: rgba(255, 255, 255, 0.15); }
.barDark > span { background: linear-gradient(90deg, #8FC7F0, #fff); }
.big { font-size: 26px; font-weight: 800; line-height: 1.1; }
.unit { font-size: 14px; font-weight: 700; color: var(--muted); }
.avatarTile { width: 44px; height: 44px; border-radius: 14px; overflow: hidden; background: var(--blue-100); }
.mid { font-size: 16px; font-weight: 800; line-height: 1.35; color: var(--navy-950); }
.small { font-size: 13px; display: inline-flex; align-items: center; gap: 4px; }
.manage { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr)); }
.manageItem {
  display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius-lg); text-decoration: none; color: var(--ink);
  background: var(--surface); border: 1px solid var(--line); box-shadow: var(--shadow);
}
.manageItem:hover { color: var(--ink); }
.manageText { display: flex; flex-direction: column; font-size: 13px; color: var(--muted); line-height: 1.3; }
.manageText strong { font-size: 20px; color: var(--navy-950); }
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
.unitCard {
  display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius-lg);
  background: var(--surface); border: 1px solid var(--line); box-shadow: var(--shadow);
}
.unitOff { background: var(--surface-2); border-style: dashed; box-shadow: none; }
.bubbleOff { background: var(--line); color: var(--muted); }
.unitText { flex: 1; min-width: 0; display: flex; flex-direction: column; align-items: flex-start; gap: 4px; }
.unitGo {
  width: 36px; height: 36px; flex: none; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center;
  color: var(--navy-700); background: var(--blue-100); transition: background 0.2s, color 0.2s, transform 0.2s var(--ease);
}
.unitGo:hover { background: var(--navy-700); color: #fff; transform: translateX(2px); }
@media (max-width: 700px) { .hero { padding: 24px 20px; } .title { font-size: 26px; } .heroArt { display: none; } }
</style>
