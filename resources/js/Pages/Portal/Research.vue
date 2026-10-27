<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '../../Components/PageHeader.vue';
import Pagination from '../../Components/Pagination.vue';
import Icon from '../../Components/Icon.vue';

defineProps({ featured: Object, reports: Object, topics: Array, topic: String });
</script>

<template>
    <Head title="Research e inteligencia" />
    <PageHeader title="Research e inteligencia" intro="Reportes y análisis del mercado de activos virtuales incluidos en la suscripción de su institución." />

    <section v-if="featured && !topic" :class="$style.featured">
        <div class="stack" :class="$style.featText">
            <span :class="$style.kicker">Reporte destacado</span>
            <h2 :class="$style.featTitle">{{ featured.title }}</h2>
            <p :class="$style.featSummary">{{ featured.summary }}</p>
            <span :class="$style.featMeta">{{ featured.author }}<template v-if="featured.minutes"> · lectura de {{ featured.minutes }} min</template> · {{ featured.date }}</span>
            <a v-if="featured.hasFile" :href="`/research/${featured.id}/abrir`" target="_blank" rel="noopener" :class="$style.featBtn">
                <Icon name="research" :size="18" />Leer reporte
            </a>
        </div>
    </section>

    <nav class="chips" aria-label="Filtrar por tema">
        <Link href="/research" class="chip" :aria-current="!topic ? 'true' : undefined">Todos</Link>
        <Link v-for="t in topics" :key="t" :href="`/research?tema=${encodeURIComponent(t)}`" class="chip" :aria-current="topic === t ? 'true' : undefined">{{ t }}</Link>
    </nav>

    <div class="grid-3">
        <article v-for="r in reports.data" :key="r.id" :class="$style.report">
            <div class="row between">
                <span class="eyebrow">{{ r.topic }}</span>
                <span class="muted" :class="$style.small">{{ r.date }}</span>
            </div>
            <h3 :class="$style.title">{{ r.title }}</h3>
            <p v-if="r.summary" :class="$style.summary">{{ r.summary }}</p>
            <div class="row between" :class="$style.foot">
                <span :class="['badge', r.exclusive ? 'badge-navy' : 'badge-blue']">{{ r.exclusive ? 'Exclusivo de su institución' : 'Incluido' }}</span>
                <a v-if="r.hasFile" :href="`/research/${r.id}/abrir`" target="_blank" rel="noopener" class="link-arrow">Leer →</a>
            </div>
        </article>
    </div>
    <p v-if="!reports.data.length" class="card muted">No hay reportes en este tema todavía.</p>
    <Pagination :links="reports.links" />
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
