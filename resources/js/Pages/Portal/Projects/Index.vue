<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '../../../Components/PageHeader.vue';
import StatusBadge from '../../../Components/StatusBadge.vue';

defineProps({ projects: Array });
</script>

<template>
    <Head title="Proyectos" />
    <PageHeader title="Proyectos" intro="Avance, fases, entregables y bitácora de cada proyecto que Blockfinity ejecuta para su institución." />
    <p v-if="!projects.length" class="card muted">Aún no hay proyectos registrados.</p>
    <div class="grid-2">
        <Link v-for="p in projects" :key="p.id" :href="`/proyectos/${p.id}`" :class="$style.card">
            <div class="row between">
                <span class="badge badge-navy">{{ p.unit ?? 'Blockfinity' }}</span>
                <StatusBadge :status="p.status" />
            </div>
            <h2 :class="$style.name">{{ p.name }}</h2>
            <span v-if="p.phaseCount" class="muted">
                <template v-if="p.phaseIndex !== false && p.phaseIndex !== -1">Fase {{ p.phaseIndex + 1 }} de {{ p.phaseCount }} · {{ p.phase }}</template>
                <template v-else>{{ p.phaseCount }} fases</template>
            </span>
            <div :class="$style.bar"><span :style="{ width: p.progress + '%' }" /></div>
            <div class="row between">
                <span class="muted">{{ p.nextMilestone ? 'Próximo hito: ' + p.nextMilestone : '' }}</span>
                <span class="mono">{{ p.progress }}%</span>
            </div>
        </Link>
    </div>
</template>

<style module>
.card {
  background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 22px;
  display: flex; flex-direction: column; gap: 12px; text-decoration: none; color: var(--ink);
}
.card:hover { border-color: var(--navy-700); color: var(--ink); }
.name { font-size: 19px; }
.bar { height: 8px; border-radius: 99px; background: var(--blue-100); overflow: hidden; }
.bar span { display: block; height: 100%; background: var(--navy-700); }
</style>
