<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Icon from '../../Components/Icon.vue';

const props = defineProps({
    today: String, projects: Array, pendingApprovals: Number, report: Object, webinar: Object,
    invoices: Object, hours: Number, level: Object, units: Array,
});
const page = usePage();
const firstName = computed(() => page.props.auth.user.name.split(' ')[0]);
const institution = computed(() => page.props.institution.name);
const greeting = computed(() => {
    const h = new Date().getHours();
    return h < 12 ? 'Buenos días' : h < 19 ? 'Buenas tardes' : 'Buenas noches';
});
const levelPct = computed(() => {
    const next = props.level.next;
    if (!next) return 100;
    return Math.min(100, Math.round((props.level.points / next.points) * 100));
});
const activeUnits = computed(() => props.units.filter((u) => u.active));
const fmtHours = (h) => String(h).replace('.', ',');
const unitLink = { BCS: '/proyectos', NEXUM: '/academia', RESEARCH: '/research' };
</script>

<template>
    <Head title="Inicio" />

    <section :class="$style.hero">
        <span :class="$style.date">{{ today }}</span>
        <h1 :class="$style.title">{{ greeting }}, {{ firstName }}.</h1>
        <p :class="$style.lead">
            Blockfinity avanza en {{ projects.length }} {{ projects.length === 1 ? 'proyecto' : 'proyectos' }} para {{ institution.replace(/\.$/, '') }}.
            <template v-if="pendingApprovals">Tiene {{ pendingApprovals }} {{ pendingApprovals === 1 ? 'aprobación pendiente' : 'aprobaciones pendientes' }}.</template>
        </p>
        <div class="row">
            <Link v-if="pendingApprovals" href="/entregables" class="btn btn-primary">Revisar aprobación pendiente</Link>
            <Link v-if="report" href="/research" class="btn btn-secondary">Leer el nuevo reporte</Link>
        </div>
    </section>

    <section :class="$style.tiles" aria-label="Resumen">
        <div :class="[$style.tile, $style.tileDark]">
            <span class="eyebrow" :class="$style.onDark">Nivel de alianza</span>
            <strong :class="$style.big">{{ level.current.name }}</strong>
            <div :class="$style.bar" role="progressbar" :aria-valuenow="levelPct" aria-valuemin="0" aria-valuemax="100"><span :style="{ width: levelPct + '%' }" /></div>
            <span :class="$style.onDarkMuted">
                {{ level.points }} <template v-if="level.next">/ {{ level.next.points }} puntos para {{ level.next.name }}</template><template v-else>puntos · nivel máximo</template>
            </span>
            <Link href="/credencial" :class="$style.darkLink">Ver beneficios de su nivel →</Link>
        </div>
        <div :class="$style.tile">
            <span class="eyebrow">Bolsa de horas</span>
            <strong :class="$style.big">{{ fmtHours(hours) }} h</strong>
            <span class="muted">disponibles para consultas y emergencias</span>
            <Link href="/bolsa-de-horas" class="link-arrow">Canjear horas →</Link>
        </div>
        <div v-if="webinar" :class="$style.tile">
            <span class="eyebrow">Webinar en vivo · {{ webinar.date }}</span>
            <strong :class="$style.mid">{{ webinar.title }}</strong>
            <Link :href="`/academia/${webinar.id}`" class="link-arrow">Reservar mi lugar →</Link>
        </div>
        <div :class="$style.tile">
            <span class="eyebrow">Facturación</span>
            <strong :class="$style.mid">{{ invoices.count ? `${invoices.count} factura(s) pendiente(s)` : 'Sin pagos pendientes' }}</strong>
            <span v-if="invoices.next" class="muted">Vence el {{ invoices.next.due }} · {{ invoices.next.currency === 'BOB' ? 'Bs' : invoices.next.currency }} {{ invoices.next.amount }}</span>
            <Link href="/facturacion" class="link-arrow">Ver facturas y contratos →</Link>
        </div>
    </section>

    <section class="stack">
        <div class="row between">
            <div class="stack" :class="$style.tight">
                <h2>Su ecosistema Blockfinity</h2>
                <p class="muted">Las unidades contratadas por su institución están activas. Las demás le muestran cómo podrían sumar valor.</p>
            </div>
            <span class="badge badge-outline">{{ activeUnits.length }} activas · {{ units.length - activeUnits.length }} por descubrir</span>
        </div>
        <div class="grid-3">
            <article v-for="u in units" :key="u.code" :class="[$style.unit, !u.active && $style.unitOff]">
                <div class="row between">
                    <span class="mono" :class="$style.code">{{ u.code }}</span>
                    <span :class="['badge', u.active ? 'badge-blue' : 'badge-outline']">{{ u.active ? 'Activa' : 'No contratada' }}</span>
                </div>
                <h3>{{ u.name }}</h3>
                <p :class="$style.unitDesc">{{ u.description }}</p>
                <span v-if="u.note" class="muted" :class="$style.note">{{ u.note }}</span>
                <Link v-if="u.active && unitLink[u.code]" :href="unitLink[u.code]" class="link-arrow">Ir a la sección →</Link>
                <a v-else-if="!u.active && $page.props.accountManager" :href="`mailto:${$page.props.accountManager.email}?subject=${encodeURIComponent('Me interesa ' + u.name)}`" class="link-arrow">Quiero saber más →</a>
            </article>
        </div>
    </section>

    <section class="grid-2">
        <div class="card stack">
            <div class="row between">
                <h2>Proyectos en curso</h2>
                <Link href="/proyectos" class="link-arrow">Ver todos →</Link>
            </div>
            <p v-if="!projects.length" class="muted">No hay proyectos activos en este momento.</p>
            <Link v-for="p in projects" :key="p.id" :href="`/proyectos/${p.id}`" :class="$style.project">
                <div class="row between">
                    <span class="badge badge-navy">{{ p.unit }}</span>
                    <span class="mono muted">{{ p.progress }}%</span>
                </div>
                <strong>{{ p.name }}</strong>
                <div :class="$style.barLight"><span :style="{ width: p.progress + '%' }" /></div>
                <span v-if="p.phase || p.nextMilestone" class="muted" :class="$style.note">
                    <template v-if="p.phase">Fase actual: {{ p.phase }}. </template>
                    <template v-if="p.nextMilestone">Próximo hito: {{ p.nextMilestone }}</template>
                </span>
            </Link>
        </div>
        <div v-if="report" class="card stack" :class="$style.reportCard">
            <span class="eyebrow">Nuevo · Research</span>
            <h2>{{ report.title }}</h2>
            <p :class="$style.unitDesc">{{ report.summary }}</p>
            <span class="muted" :class="$style.note">{{ report.author }}<template v-if="report.minutes"> · lectura de {{ report.minutes }} min</template></span>
            <a :href="`/research/${report.id}/abrir`" target="_blank" rel="noopener" class="btn btn-primary" :class="$style.self">
                <Icon name="research" :size="18" />Abrir reporte
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
