<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Paginacion from '../../Componentes/Componente_Paginacion.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';
import Componente_Estado_Vacio from '../../Componentes/Componente_Estado_Vacio.vue';

defineProps({ Reporte_Destacado: Object, Reportes: Object, Temas: Array, Tema_Seleccionado: String });
</script>

<template>
    <Head title="Research e inteligencia" />
    <Componente_Encabezado_Pagina Icono="grafico" Titulo="Research" Introduccion="Reportes del mercado de activos virtuales." />

    <section v-if="Reporte_Destacado && !Tema_Seleccionado" :class="$style.featured">
        <span :class="$style.featArt" aria-hidden="true"><Componente_Icono Nombre="grafico" :Tamano="160" :Grosor="0.8" /></span>
        <div class="stack" :class="$style.featText">
            <span :class="$style.kicker"><Componente_Icono Nombre="estrella" :Tamano="14" />Destacado</span>
            <h2 :class="$style.featTitle">{{ Reporte_Destacado.Titulo }}</h2>
            <p :class="$style.featSummary">{{ Reporte_Destacado.Resumen }}</p>
            <span :class="$style.featMeta">{{ Reporte_Destacado.Autor }}<template v-if="Reporte_Destacado.Minutos_Lectura"> · lectura de {{ Reporte_Destacado.Minutos_Lectura }} min</template> · {{ Reporte_Destacado.Fecha }}</span>
            <Link :href="`/research/${Reporte_Destacado.Id}`" :class="$style.featBtn">
                <Componente_Icono Nombre="grafico" :Tamano="18" />Leer reporte
            </Link>
        </div>
    </section>

    <nav class="chips" aria-label="Filtrar por tema">
        <Link href="/research" class="chip" :aria-current="!Tema_Seleccionado ? 'true' : undefined">Todos</Link>
        <Link v-for="Tema in Temas" :key="Tema" :href="`/research?tema=${encodeURIComponent(Tema)}`" class="chip" :aria-current="Tema_Seleccionado === Tema ? 'true' : undefined">{{ Tema }}</Link>
    </nav>

    <div class="grid-3 escalonado">
        <article v-for="Reporte in Reportes.data" :key="Reporte.Id" :class="$style.report" class="elevable">
            <div class="row between">
                <span class="eyebrow">{{ Reporte.Tema }}</span>
                <span class="muted" :class="$style.small">{{ Reporte.Fecha }}</span>
            </div>
            <h3 :class="$style.title">{{ Reporte.Titulo }}</h3>
            <p v-if="Reporte.Resumen" :class="$style.summary">{{ Reporte.Resumen }}</p>
            <span class="row" :class="$style.meta">
                <span v-if="Reporte.Minutos_Lectura" class="muted" :class="$style.small"><Componente_Icono Nombre="reloj" :Tamano="13" /> {{ Reporte.Minutos_Lectura }} min</span>
            </span>
            <div class="row between" :class="$style.foot">
                <span :class="['badge', Reporte.Es_Exclusivo ? 'badge-navy' : 'badge-blue']">{{ Reporte.Es_Exclusivo ? 'Exclusivo' : 'Incluido' }}</span>
                <Link :href="`/research/${Reporte.Id}`" class="link-arrow">Leer <Componente_Icono Nombre="flecha" :Tamano="16" /></Link>
            </div>
        </article>
    </div>
    <Componente_Estado_Vacio v-if="!Reportes.data.length" Icono="grafico" Texto="No hay reportes en este tema todavía" />
    <Componente_Paginacion :Enlaces="Reportes.links" />
</template>

<style module>
.featured { position: relative; overflow: hidden; background: var(--gradient-hero); border-radius: 24px; padding: 32px; color: #fff; box-shadow: 0 18px 40px rgba(15, 34, 64, 0.22); }
.featArt { position: absolute; right: 20px; bottom: -20px; color: rgba(255, 255, 255, 0.08); }
.featText { position: relative; max-width: 760px; }
.kicker { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: var(--side-label); }
.featTitle { color: #fff; font-size: 28px; }
.featSummary { color: var(--side-text); font-size: 16px; line-height: 1.6; }
.featMeta { color: var(--side-label); font-size: 13px; }
.featBtn {
  align-self: flex-start; display: inline-flex; align-items: center; gap: 8px; min-height: 46px; padding: 0 18px;
  border-radius: 10px; background: #fff; color: var(--navy-950); font-weight: 800; text-decoration: none;
}
.report { background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 20px; display: flex; flex-direction: column; gap: 10px; box-shadow: var(--shadow); }
.title { font-size: 17px; line-height: 1.35; }
.summary { font-size: 14px; color: var(--text-2); display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
.foot { margin-top: auto; }
.small { font-size: 12px; display: inline-flex; align-items: center; gap: 4px; }
.meta { gap: 8px; }
</style>
