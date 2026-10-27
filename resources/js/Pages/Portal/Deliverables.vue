<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import PageHeader from '../../Components/PageHeader.vue';
import StatusBadge from '../../Components/StatusBadge.vue';
import Pagination from '../../Components/Pagination.vue';
import FileHasher from '../../Components/FileHasher.vue';
import DeliverableActions from '../../Components/DeliverableActions.vue';
import ApprovalPanel from '../../Components/ApprovalPanel.vue';
import Icon from '../../Components/Icon.vue';

const props = defineProps({ deliverables: Object, kinds: Array, projects: Array, filters: Object, access: Array });
const page = usePage();
const filters = reactive({ kind: props.filters.kind ?? '', project: props.filters.project ?? '' });
const verification = computed(() => page.props.flash?.verification);
const pending = computed(() => props.deliverables.data.filter((d) => d.status === 'pending_approval'));
const checking = ref(false);

function apply() {
    router.get('/entregables', Object.fromEntries(Object.entries(filters).filter(([, v]) => v)), { preserveState: true, preserveScroll: true });
}
function setKind(k) { filters.kind = filters.kind === k ? '' : k; apply(); }
function onHashed({ hash }) {
    checking.value = true;
    router.post('/entregables/verificar', { sha256: hash }, { preserveScroll: true, onFinish: () => { checking.value = false; } });
}
</script>

<template>
    <Head title="Historial de entregables" />
    <PageHeader eyebrow="Bóveda de entregables" title="Historial de entregables"
                intro="Informes, presentaciones, auditorías y plantillas de todos los proyectos con Blockfinity, siempre disponibles para su institución." />

    <ApprovalPanel v-for="d in pending" :key="d.id" :deliverable="d" />

    <div class="chips" role="group" aria-label="Filtrar por tipo">
        <button v-for="k in kinds" :key="k.key" type="button" class="chip" :aria-pressed="filters.kind === k.key ? 'true' : 'false'" @click="setKind(k.key)">
            {{ k.label }} · {{ k.count }}
        </button>
    </div>

    <div :class="$style.layout">
        <section class="stack" :class="$style.list">
            <div class="row between">
                <h2>{{ filters.kind ? kinds.find((k) => k.key === filters.kind)?.label : 'Todos los documentos' }}</h2>
                <label class="row">
                    <span class="label">Proyecto</span>
                    <select v-model="filters.project" class="select" :class="$style.select" @change="apply">
                        <option value="">Todos los proyectos</option>
                        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </label>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Documento</th><th>Unidad</th><th>Fecha</th><th>Estado</th><th>Huella</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                    <tbody>
                        <tr v-for="d in deliverables.data" :key="d.id">
                            <td><strong>{{ d.title }}</strong><div class="muted" :class="$style.small">{{ d.kindLabel }}<template v-if="d.version"> · v{{ d.version }}</template><template v-if="d.project"> · {{ d.project }}</template></div></td>
                            <td><span class="badge badge-outline">{{ d.unit ?? '—' }}</span></td>
                            <td>{{ d.date }}</td>
                            <td><StatusBadge :status="d.status" /></td>
                            <td class="mono" :class="$style.small" :title="d.sha256">{{ d.sha256 ? d.sha256.slice(0, 8) + '…' : '—' }}</td>
                            <td><DeliverableActions :deliverable="d" /></td>
                        </tr>
                        <tr v-if="!deliverables.data.length"><td colspan="6" class="muted">No hay documentos con estos filtros.</td></tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="deliverables.links" />
        </section>

        <aside class="stack" :class="$style.side">
            <div class="card stack">
                <div class="row"><Icon name="fingerprint" /><h3>Verificar un documento</h3></div>
                <p class="muted" :class="$style.small">¿Recibió una copia de un entregable? Compruebe que no fue alterado comparando su huella con el registro.</p>
                <FileHasher @hashed="onHashed" />
                <p v-if="checking" class="muted">Comparando…</p>
                <div v-else-if="verification" :class="[$style.result, verification.ok ? $style.ok : $style.bad]" role="status">
                    <strong>{{ verification.ok ? 'Documento auténtico' : 'Sin coincidencias' }}</strong>
                    <span v-if="verification.ok">Coincide con «{{ verification.title }}» ({{ verification.date }}).</span>
                    <span v-else>La huella no coincide con ningún documento registrado. Puede haber sido modificado.</span>
                    <span class="mono" :class="$style.hash">{{ verification.hash }}</span>
                </div>
            </div>
            <div v-if="access.length" class="card stack">
                <h3>Registro de accesos</h3>
                <ul :class="$style.access">
                    <li v-for="(a, i) in access" :key="i"><strong>{{ a.who }}</strong><span>{{ a.what }}</span><span class="muted">{{ a.when }}</span></li>
                </ul>
            </div>
        </aside>
    </div>
</template>

<style module>
.layout { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; }
.list { flex: 999 1 560px; min-width: 0; }
.side { flex: 1 1 300px; min-width: 0; }
.select { width: auto; min-width: 200px; }
.small { font-size: 12px; }
.result { display: flex; flex-direction: column; gap: 4px; padding: 12px; border-radius: 10px; font-size: 14px; }
.ok { background: var(--ok-bg); color: var(--ok-fg); }
.bad { background: var(--danger-bg); color: var(--danger-fg); }
.hash { font-size: 11px; word-break: break-all; opacity: 0.8; }
.access { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; font-size: 13px; }
.access li { display: flex; flex-direction: column; border-bottom: 1px solid #E8EBEF; padding-bottom: 8px; }
</style>
