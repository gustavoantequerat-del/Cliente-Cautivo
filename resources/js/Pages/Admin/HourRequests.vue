<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import StatusBadge from '../../Components/StatusBadge.vue';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({ requests: Object, statuses: Object, filter: String });
const drafts = reactive(Object.fromEntries(props.requests.data.map((r) => [r.id, {
    status: r.status, charged_hours: r.charged ?? r.estimated, response: r.response ?? '',
}])));
const save = (id) => router.put(`/admin/solicitudes-horas/${id}`, drafts[id], { preserveScroll: true });
</script>

<template>
    <Head title="Solicitudes de horas" />
    <h1>Solicitudes de horas</h1>
    <div class="chips">
        <Link href="/admin/solicitudes-horas" class="chip" :aria-current="filter === 'abiertas' ? 'true' : undefined">Abiertas</Link>
        <Link href="/admin/solicitudes-horas?estado=todas" class="chip" :aria-current="filter === 'todas' ? 'true' : undefined">Todas</Link>
    </div>
    <p v-if="!requests.data.length" class="card muted">No hay solicitudes.</p>
    <article v-for="r in requests.data" :key="r.id" class="card stack">
        <div class="row between">
            <div class="row">
                <span :class="['badge', r.mode === 'emergencia' ? 'badge-danger' : 'badge-blue']">{{ r.mode }}</span>
                <strong>{{ r.institution }}</strong>
                <span class="muted">{{ r.user }} · <a :href="`mailto:${r.email}`">{{ r.email }}</a> · {{ r.date }}</span>
            </div>
            <StatusBadge :status="r.status" />
        </div>
        <p><strong>{{ r.specialty }}</strong> · estimadas {{ r.estimated }} h · saldo de la institución {{ r.balance }} h</p>
        <p :class="$style.desc">{{ r.description }}</p>
        <form :class="$style.form" @submit.prevent="save(r.id)">
            <div class="field">
                <label :for="`st-${r.id}`">Estado</label>
                <select :id="`st-${r.id}`" v-model="drafts[r.id].status" class="select">
                    <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                </select>
            </div>
            <div class="field">
                <label :for="`h-${r.id}`">Horas a descontar</label>
                <input :id="`h-${r.id}`" v-model="drafts[r.id].charged_hours" type="number" step="0.25" min="0" class="input" :disabled="r.status === 'done'">
                <span class="help">Se descuentan al marcar «Atendida».</span>
            </div>
            <div class="field" :class="$style.wide">
                <label :for="`resp-${r.id}`">Respuesta visible para el cliente</label>
                <textarea :id="`resp-${r.id}`" v-model="drafts[r.id].response" class="textarea" />
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </article>
    <Pagination :links="requests.links" />
</template>

<style module>
.desc { white-space: pre-line; background: var(--surface-2); border-radius: 10px; padding: 12px; font-size: 14px; }
.form { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr)); align-items: end; }
.wide { grid-column: 1 / -1; }
</style>
