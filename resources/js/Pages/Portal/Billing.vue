<script setup>
import { ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import PageHeader from '../../Components/PageHeader.vue';
import StatusBadge from '../../Components/StatusBadge.vue';
import Icon from '../../Components/Icon.vue';

defineProps({ kpis: Array, invoices: Array, contracts: Array, billing: Array });
const tab = ref('invoices');
const manager = usePage().props.accountManager;
</script>

<template>
    <Head title="Facturación y contratos" />
    <PageHeader title="Facturación y contratos" intro="Facturas electrónicas, presupuestos firmados y contratos de su institución, con su historial completo." />

    <section :class="$style.kpis">
        <div v-for="k in kpis" :key="k.label" :class="$style.kpi">
            <span class="eyebrow">{{ k.label }}</span>
            <strong :class="$style.value">{{ k.value }}</strong>
            <span v-if="k.sub" class="muted">{{ k.sub }}</span>
        </div>
    </section>

    <div :class="$style.layout">
        <section class="stack" :class="$style.main">
            <div class="chips" role="tablist" aria-label="Documentos">
                <button type="button" role="tab" class="chip" :aria-selected="tab === 'invoices'" :aria-pressed="tab === 'invoices' ? 'true' : 'false'" @click="tab = 'invoices'">Facturas · {{ invoices.length }}</button>
                <button type="button" role="tab" class="chip" :aria-selected="tab === 'contracts'" :aria-pressed="tab === 'contracts' ? 'true' : 'false'" @click="tab = 'contracts'">Contratos y presupuestos · {{ contracts.length }}</button>
            </div>

            <div v-if="tab === 'invoices'" class="table-wrap">
                <table class="table">
                    <thead><tr><th>N.º</th><th>Concepto</th><th>Emisión</th><th>Vence</th><th>Monto</th><th>Estado</th><th>Archivos</th></tr></thead>
                    <tbody>
                        <tr v-for="f in invoices" :key="f.id">
                            <td class="mono">{{ f.number }}</td>
                            <td>{{ f.concept }}</td>
                            <td>{{ f.issued }}</td>
                            <td>{{ f.due ?? '—' }}</td>
                            <td class="mono" :class="$style.nowrap">{{ f.currency === 'BOB' ? 'Bs' : f.currency }} {{ f.amount }}</td>
                            <td><StatusBadge :status="f.status" /></td>
                            <td :class="$style.nowrap">
                                <a v-if="f.hasPdf" :href="`/facturacion/factura/${f.id}/pdf`" class="btn btn-secondary btn-sm">PDF</a>
                                <a v-if="f.hasXml" :href="`/facturacion/factura/${f.id}/xml`" class="btn btn-secondary btn-sm" :class="$style.gap">XML</a>
                                <span v-if="!f.hasPdf && !f.hasXml" class="muted">—</span>
                            </td>
                        </tr>
                        <tr v-if="!invoices.length"><td colspan="7" class="muted">Aún no hay facturas registradas.</td></tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="stack">
                <article v-for="c in contracts" :key="c.id" :class="$style.contract">
                    <div class="stack" :class="$style.cText">
                        <div class="row">
                            <span class="badge badge-navy">{{ c.unit ?? 'Blockfinity' }}</span>
                            <span class="badge badge-outline">{{ c.kind === 'quote' ? 'Presupuesto' : 'Contrato' }}</span>
                        </div>
                        <strong>{{ c.title }}</strong>
                        <span class="muted" :class="$style.small">
                            <template v-if="c.signed">Firmado el {{ c.signed }}</template><template v-if="c.expires"> · vigente hasta {{ c.expires }}</template><template v-if="c.amount"> · Bs {{ c.amount }}</template>
                        </span>
                        <span v-if="c.sha256" class="mono muted" :class="$style.small">Huella {{ c.sha256.slice(0, 16) }}…</span>
                    </div>
                    <a v-if="c.hasFile" :href="`/facturacion/contrato/${c.id}`" target="_blank" rel="noopener" class="btn btn-secondary btn-sm"><Icon name="download" :size="16" />Descargar</a>
                </article>
                <p v-if="!contracts.length" class="card muted">Aún no hay contratos registrados.</p>
            </div>
            <p class="muted" :class="$style.small">Facturación electrónica emitida por Blockfinity Advisors S.R.L. en Bolivia, válida para crédito fiscal.</p>
        </section>

        <aside class="stack" :class="$style.side">
            <div class="card stack">
                <h3>Datos de facturación</h3>
                <dl :class="$style.dl">
                    <div v-for="b in billing" :key="b.k"><dt>{{ b.k }}</dt><dd>{{ b.v }}</dd></div>
                </dl>
                <a v-if="manager" :href="`mailto:${manager.email}?subject=${encodeURIComponent('Cambio de datos de facturación')}`" class="link-arrow">Solicitar cambio de datos →</a>
            </div>
        </aside>
    </div>
</template>

<style module>
.kpis { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr)); }
.kpi { background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 20px; display: flex; flex-direction: column; gap: 6px; }
.value { font-size: 24px; font-weight: 800; color: var(--navy-950); }
.layout { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; }
.main { flex: 999 1 560px; min-width: 0; }
.side { flex: 1 1 280px; min-width: 0; }
.nowrap { white-space: nowrap; }
.gap { margin-left: 6px; }
.small { font-size: 12px; }
.contract { background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius); padding: 16px; display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: space-between; }
.cText { gap: 6px; flex: 1 1 300px; }
.dl { margin: 0; display: flex; flex-direction: column; gap: 10px; }
.dl div { display: flex; flex-direction: column; gap: 2px; }
.dl dt { font-size: 12px; color: var(--muted); }
.dl dd { margin: 0; font-weight: 700; font-size: 14px; }
</style>
