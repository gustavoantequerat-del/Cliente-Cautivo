<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

const Propiedades = defineProps({
    Fecha_Hoy: String, Proyectos_Activos: Array, Cantidad_Aprobaciones_Pendientes: Number, Reporte_Destacado: Object,
    Proximo_Webinar: Object, Facturas_Pendientes: Object, Saldo_Horas: Number, Nivel_Alianza: Object, Unidades_Negocio: Array,
});
const Pagina = usePage();
const Primer_Nombre = computed(() => Pagina.props.Usuario_Actual.Nombre.split(' ')[0]);
const Nombre_Institucion = computed(() => Pagina.props.Institucion_Actual.Nombre.replace(/\.$/, ''));
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
const Formatear_Horas = (Horas) => String(Horas).replace('.', ',');
const Formatear_Moneda = (Moneda) => (Moneda === 'BOB' ? 'Bs' : Moneda);
const Seccion_Por_Unidad = { BCS: '/proyectos', NEXUM: '/academia', RESEARCH: '/research' };
</script>

<template>
    <Head title="Inicio" />

    <section :class="$style.hero">
        <span :class="$style.date">{{ Fecha_Hoy }}</span>
        <h1 :class="$style.title">{{ Saludo }}, {{ Primer_Nombre }}.</h1>
        <p :class="$style.lead">
            Blockfinity avanza en {{ Proyectos_Activos.length }} {{ Proyectos_Activos.length === 1 ? 'proyecto' : 'proyectos' }} para {{ Nombre_Institucion }}.
            <template v-if="Cantidad_Aprobaciones_Pendientes">Tiene {{ Cantidad_Aprobaciones_Pendientes }} {{ Cantidad_Aprobaciones_Pendientes === 1 ? 'aprobación pendiente' : 'aprobaciones pendientes' }}.</template>
        </p>
        <div class="row">
            <Link v-if="Cantidad_Aprobaciones_Pendientes" href="/entregables" class="btn btn-primary">Revisar aprobación pendiente</Link>
            <Link v-if="Reporte_Destacado" href="/research" class="btn btn-secondary">Leer el nuevo reporte</Link>
        </div>
    </section>

    <section :class="$style.tiles" aria-label="Resumen">
        <div :class="[$style.tile, $style.tileDark]">
            <span class="eyebrow" :class="$style.onDark">Nivel de alianza</span>
            <strong :class="$style.big">{{ Nivel_Alianza.Nivel_Actual.Nombre }}</strong>
            <div :class="$style.bar" role="progressbar" :aria-valuenow="Porcentaje_Nivel" aria-valuemin="0" aria-valuemax="100"><span :style="{ width: Porcentaje_Nivel + '%' }" /></div>
            <span :class="$style.onDarkMuted">
                {{ Nivel_Alianza.Puntos }} <template v-if="Nivel_Alianza.Nivel_Siguiente">/ {{ Nivel_Alianza.Nivel_Siguiente.Puntos_Minimos }} puntos para {{ Nivel_Alianza.Nivel_Siguiente.Nombre }}</template><template v-else>puntos · nivel máximo</template>
            </span>
            <Link href="/credencial" :class="$style.darkLink">Ver beneficios de su nivel →</Link>
        </div>
        <div :class="$style.tile">
            <span class="eyebrow">Bolsa de horas</span>
            <strong :class="$style.big">{{ Formatear_Horas(Saldo_Horas) }} h</strong>
            <span class="muted">disponibles para consultas y emergencias</span>
            <Link href="/bolsa-de-horas" class="link-arrow">Canjear horas →</Link>
        </div>
        <div v-if="Proximo_Webinar" :class="$style.tile">
            <span class="eyebrow">Webinar en vivo · {{ Proximo_Webinar.Fecha }}</span>
            <strong :class="$style.mid">{{ Proximo_Webinar.Titulo }}</strong>
            <Link :href="`/academia/${Proximo_Webinar.Id}`" class="link-arrow">Reservar mi lugar →</Link>
        </div>
        <div :class="$style.tile">
            <span class="eyebrow">Facturación</span>
            <strong :class="$style.mid">{{ Facturas_Pendientes.Cantidad ? `${Facturas_Pendientes.Cantidad} factura(s) pendiente(s)` : 'Sin pagos pendientes' }}</strong>
            <span v-if="Facturas_Pendientes.Proxima" class="muted">Vence el {{ Facturas_Pendientes.Proxima.Fecha_Vencimiento }} · {{ Formatear_Moneda(Facturas_Pendientes.Proxima.Moneda) }} {{ Facturas_Pendientes.Proxima.Monto }}</span>
            <Link href="/facturacion" class="link-arrow">Ver facturas y contratos →</Link>
        </div>
    </section>

    <section class="stack">
        <div class="row between">
            <div class="stack" :class="$style.tight">
                <h2>Su ecosistema Blockfinity</h2>
                <p class="muted">Las unidades contratadas por su institución están activas. Las demás le muestran cómo podrían sumar valor.</p>
            </div>
            <span class="badge badge-outline">{{ Unidades_Contratadas.length }} activas · {{ Unidades_Negocio.length - Unidades_Contratadas.length }} por descubrir</span>
        </div>
        <div class="grid-3">
            <article v-for="Unidad in Unidades_Negocio" :key="Unidad.Codigo" :class="[$style.unit, !Unidad.Esta_Contratada && $style.unitOff]">
                <div class="row between">
                    <span class="mono" :class="$style.code">{{ Unidad.Codigo }}</span>
                    <span :class="['badge', Unidad.Esta_Contratada ? 'badge-blue' : 'badge-outline']">{{ Unidad.Esta_Contratada ? 'Activa' : 'No contratada' }}</span>
                </div>
                <h3>{{ Unidad.Nombre }}</h3>
                <p :class="$style.unitDesc">{{ Unidad.Descripcion }}</p>
                <span v-if="Unidad.Nota" class="muted" :class="$style.note">{{ Unidad.Nota }}</span>
                <Link v-if="Unidad.Esta_Contratada && Seccion_Por_Unidad[Unidad.Codigo]" :href="Seccion_Por_Unidad[Unidad.Codigo]" class="link-arrow">Ir a la sección →</Link>
                <a v-else-if="!Unidad.Esta_Contratada && Ejecutivo_Cuenta" :href="`mailto:${Ejecutivo_Cuenta.Email}?subject=${encodeURIComponent('Me interesa ' + Unidad.Nombre)}`" class="link-arrow">Quiero saber más →</a>
            </article>
        </div>
    </section>

    <section class="grid-2">
        <div class="card stack">
            <div class="row between">
                <h2>Proyectos en curso</h2>
                <Link href="/proyectos" class="link-arrow">Ver todos →</Link>
            </div>
            <p v-if="!Proyectos_Activos.length" class="muted">No hay proyectos activos en este momento.</p>
            <Link v-for="Proyecto in Proyectos_Activos" :key="Proyecto.Id" :href="`/proyectos/${Proyecto.Id}`" :class="$style.project">
                <div class="row between">
                    <span class="badge badge-navy">{{ Proyecto.Codigo_Unidad }}</span>
                    <span class="mono muted">{{ Proyecto.Avance }}%</span>
                </div>
                <strong>{{ Proyecto.Nombre }}</strong>
                <div :class="$style.barLight"><span :style="{ width: Proyecto.Avance + '%' }" /></div>
                <span v-if="Proyecto.Fase_Actual || Proyecto.Proximo_Hito" class="muted" :class="$style.note">
                    <template v-if="Proyecto.Fase_Actual">Fase actual: {{ Proyecto.Fase_Actual }}. </template>
                    <template v-if="Proyecto.Proximo_Hito">Próximo hito: {{ Proyecto.Proximo_Hito }}</template>
                </span>
            </Link>
        </div>
        <div v-if="Reporte_Destacado" class="card stack" :class="$style.reportCard">
            <span class="eyebrow">Nuevo · Research</span>
            <h2>{{ Reporte_Destacado.Titulo }}</h2>
            <p :class="$style.unitDesc">{{ Reporte_Destacado.Resumen }}</p>
            <span class="muted" :class="$style.note">{{ Reporte_Destacado.Autor }}<template v-if="Reporte_Destacado.Minutos_Lectura"> · lectura de {{ Reporte_Destacado.Minutos_Lectura }} min</template></span>
            <a :href="`/research/${Reporte_Destacado.Id}/abrir`" target="_blank" rel="noopener" class="btn btn-primary" :class="$style.self">
                <Componente_Icono Nombre="grafico" :Tamano="18" />Abrir reporte
            </a>
        </div>
    </section>
</template>

<style module>
.hero { display: flex; flex-direction: column; gap: 12px; }
.date { font-size: 14px; font-weight: 600; color: var(--muted); }
.title { font-size: 36px; }
.lead { font-size: 17px; color: var(--text-2); max-width: 720px; line-height: 1.6; }
.tiles { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 230px), 1fr)); }
.tile {
  background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 20px;
  display: flex; flex-direction: column; gap: 8px;
}
.tileDark { background: var(--navy-950); border-color: var(--navy-950); color: #fff; }
.onDark { color: var(--side-label); }
.onDarkMuted { font-size: 13px; color: var(--side-text); }
.darkLink { color: #fff; font-weight: 700; font-size: 14px; text-decoration: none; }
.darkLink:hover { color: var(--side-text); }
.big { font-size: 26px; font-weight: 800; line-height: 1.1; }
.mid { font-size: 16px; font-weight: 800; line-height: 1.35; color: var(--navy-950); }
.bar { height: 6px; border-radius: 99px; background: var(--navy-800); overflow: hidden; }
.bar span { display: block; height: 100%; background: #fff; border-radius: 99px; }
.barLight { height: 6px; border-radius: 99px; background: var(--blue-100); overflow: hidden; }
.barLight span { display: block; height: 100%; background: var(--navy-700); border-radius: 99px; }
.tight { gap: 4px; }
.unit { background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 20px; display: flex; flex-direction: column; gap: 10px; }
.unitOff { background: var(--surface-2); border-style: dashed; }
.code { font-size: 12px; font-weight: 600; color: var(--navy-700); }
.unitDesc { font-size: 14px; color: var(--text-2); line-height: 1.55; }
.note { font-size: 13px; }
.project {
  display: flex; flex-direction: column; gap: 8px; padding: 14px; border-radius: var(--radius); border: 1px solid var(--line);
  text-decoration: none; color: var(--ink);
}
.project:hover { border-color: var(--navy-700); color: var(--ink); }
.reportCard { background: var(--blue-100); border-color: var(--blue-100); }
.self { align-self: flex-start; }
</style>
