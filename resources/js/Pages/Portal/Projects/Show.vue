<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import StatusBadge from '../../../Components/StatusBadge.vue';
import ApprovalPanel from '../../../Components/ApprovalPanel.vue';
import DeliverableActions from '../../../Components/DeliverableActions.vue';

const props = defineProps({ project: Object, deliverables: Array });
const pending = computed(() => props.deliverables.filter((d) => d.status === 'pending_approval'));
const phaseLabel = { done: 'Completada', current: 'En curso', pending: 'Pendiente' };
</script>

<template>
    <Head :title="project.name" />
    <Link href="/proyectos" :class="$style.back">← Todos los proyectos</Link>

    <section class="stack">
        <div class="row">
            <span class="badge badge-navy">{{ project.unit }}</span>
            <StatusBadge :status="project.status" />
            <span v-if="project.startsOn" class="muted">{{ project.startsOn }}<template v-if="project.endsOn"> – {{ project.endsOn }}</template></span>
        </div>
        <h1>{{ project.name }}</h1>
        <p v-if="project.description" :class="$style.lead">{{ project.description }}</p>
    </section>

    <ol v-if="project.phases.length" :class="$style.phases" aria-label="Fases del proyecto">
        <li v-for="ph in project.phases" :key="ph.position" :class="[$style.phase, $style[ph.status]]">
            <span :class="$style.num">{{ ph.position }}</span>
            <strong>{{ ph.name }}</strong>
            <span :class="$style.state">{{ phaseLabel[ph.status] }}<template v-if="ph.due"> · {{ ph.due }}</template></span>
        </li>
    </ol>

    <ApprovalPanel v-for="d in pending" :key="d.id" :deliverable="d" />

    <section class="stack">
        <div class="stack" :class="$style.tight">
            <h2>Entregables y evidencia</h2>
            <p class="muted">Cada entregable tiene su huella digital SHA-256. Al aprobarlo, queda registrado quién, cuándo y qué versión exacta aprobó.</p>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Entregable</th><th>Fase</th><th>Fecha</th><th>Estado</th><th>Huella</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    <tr v-for="d in deliverables" :key="d.id">
                        <td><strong>{{ d.title }}</strong><div v-if="d.approvedBy" class="muted" :class="$style.small">Aprobado por {{ d.approvedBy }} · {{ d.approvedAt }}</div></td>
                        <td class="mono">{{ d.phase ?? '—' }}</td>
                        <td>{{ d.date }}</td>
                        <td><StatusBadge :status="d.status" /></td>
                        <td class="mono" :class="$style.small" :title="d.sha256">{{ d.sha256 ? d.sha256.slice(0, 8) + '…' + d.sha256.slice(-4) : '—' }}</td>
                        <td><DeliverableActions :deliverable="d" /></td>
                    </tr>
                    <tr v-if="!deliverables.length"><td colspan="6" class="muted">Aún no hay entregables publicados.</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section v-if="project.updates.length" class="card stack">
        <h2>Bitácora</h2>
        <ul :class="$style.log">
            <li v-for="(u, i) in project.updates" :key="i"><span class="mono">{{ u.date }}</span><span>{{ u.body }}</span></li>
        </ul>
    </section>
</template>

<style module>
.back { font-weight: 700; font-size: 14px; text-decoration: none; }
.lead { font-size: 16px; color: var(--text-2); max-width: 820px; line-height: 1.6; }
.tight { gap: 4px; }
.small { font-size: 12px; }
.phases { list-style: none; margin: 0; padding: 0; display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 200px), 1fr)); }
.phase { background: var(--surface); border: 1px solid var(--line); border-top: 4px solid var(--line-2); border-radius: var(--radius); padding: 16px; display: flex; flex-direction: column; gap: 6px; }
.num { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; border: 1.5px solid var(--line-2); color: var(--muted); }
.state { font-size: 13px; color: var(--muted); }
.done { border-top-color: var(--navy-700); }
.done .num { background: var(--navy-700); border-color: var(--navy-700); color: #fff; }
.current { border-top-color: var(--navy-700); }
.current .num { border-color: var(--navy-700); color: var(--navy-700); }
.log { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.log li { display: grid; grid-template-columns: 70px 1fr; gap: 12px; font-size: 14px; }
.log li span:first-child { color: var(--muted); font-size: 13px; }
</style>
