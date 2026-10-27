<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Paginacion from '../../Componentes/Componente_Paginacion.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

defineProps({ Reporte_Destacado: Object, Reportes: Object, Temas: Array, Tema_Seleccionado: String });
</script>

<template>
    <Head title="Research e inteligencia" />
    <Componente_Encabezado_Pagina Titulo="Research e inteligencia" Introduccion="Reportes y análisis del mercado de activos virtuales incluidos en la suscripción de su institución." />

    <section v-if="Reporte_Destacado && !Tema_Seleccionado" :class="$style.featured">
        <div class="stack" :class="$style.featText">
            <span :class="$style.kicker">Reporte destacado</span>
            <h2 :class="$style.featTitle">{{ Reporte_Destacado.Titulo }}</h2>
            <p :class="$style.featSummary">{{ Reporte_Destacado.Resumen }}</p>
            <span :class="$style.featMeta">{{ Reporte_Destacado.Autor }}<template v-if="Reporte_Destacado.Minutos_Lectura"> · lectura de {{ Reporte_Destacado.Minutos_Lectura }} min</template> · {{ Reporte_Destacado.Fecha }}</span>
            <a v-if="Reporte_Destacado.Tiene_Archivo" :href="`/research/${Reporte_Destacado.Id}/abrir`" target="_blank" rel="noopener" :class="$style.featBtn">
                <Componente_Icono Nombre="grafico" :Tamano="18" />Leer reporte
            </a>
        </div>
    </section>

    <nav class="chips" aria-label="Filtrar por tema">
        <Link href="/research" class="chip" :aria-current="!Tema_Seleccionado ? 'true' : undefined">Todos</Link>
        <Link v-for="Tema in Temas" :key="Tema" :href="`/research?tema=${encodeURIComponent(Tema)}`" class="chip" :aria-current="Tema_Seleccionado === Tema ? 'true' : undefined">{{ Tema }}</Link>
    </nav>

    <div class="grid-3">
        <article v-for="Reporte in Reportes.data" :key="Reporte.Id" :class="$style.report">
            <div class="row between">
                <span class="eyebrow">{{ Reporte.Tema }}</span>
                <span class="muted" :class="$style.small">{{ Reporte.Fecha }}</span>
            </div>
            <h3 :class="$style.title">{{ Reporte.Titulo }}</h3>
            <p v-if="Reporte.Resumen" :class="$style.summary">{{ Reporte.Resumen }}</p>
            <div class="row between" :class="$style.foot">
                <span :class="['badge', Reporte.Es_Exclusivo ? 'badge-navy' : 'badge-blue']">{{ Reporte.Es_Exclusivo ? 'Exclusivo de su institución' : 'Incluido' }}</span>
                <a v-if="Reporte.Tiene_Archivo" :href="`/research/${Reporte.Id}/abrir`" target="_blank" rel="noopener" class="link-arrow">Leer →</a>
            </div>
        </article>
    </div>
    <p v-if="!Reportes.data.length" class="card muted">No hay reportes en este tema todavía.</p>
    <Componente_Paginacion :Enlaces="Reportes.links" />
</template>

<style module>
.featured { background: var(--navy-950); border-radius: var(--radius-lg); padding: 32px; color: #fff; }
.featText { max-width: 760px; }
.kicker { font-size: 11px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: var(--side-label); }
.featTitle { color: #fff; font-size: 28px; }
.featSummary { color: var(--side-text); font-size: 16px; line-height: 1.6; }
.featMeta { color: var(--side-label); font-size: 13px; }
.featBtn {
  align-self: flex-start; display: inline-flex; align-items: center; gap: 8px; min-height: 46px; padding: 0 18px;
  border-radius: 10px; background: #fff; color: var(--navy-950); font-weight: 800; text-decoration: none;
}
.report { background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 20px; display: flex; flex-direction: column; gap: 10px; }
.title { font-size: 17px; line-height: 1.35; }
.summary { font-size: 14px; color: var(--text-2); }
.foot { margin-top: auto; }
.small { font-size: 12px; }
</style>
