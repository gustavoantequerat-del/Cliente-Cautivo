<script setup>
import { ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Etiqueta_Estado from '../../Componentes/Componente_Etiqueta_Estado.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

defineProps({ Indicadores: Array, Facturas: Array, Contratos: Array, Datos_Facturacion: Array });
const Pestana_Activa = ref('facturas');
const Ejecutivo_Cuenta = usePage().props.Ejecutivo_Cuenta;
const Formatear_Moneda = (Moneda) => (Moneda === 'BOB' ? 'Bs' : Moneda);
</script>

<template>
    <Head title="Facturación y contratos" />
    <Componente_Encabezado_Pagina Titulo="Facturación y contratos" Introduccion="Facturas electrónicas, presupuestos firmados y contratos de su institución, con su historial completo." />

    <section :class="$style.kpis">
        <div v-for="Indicador in Indicadores" :key="Indicador.Etiqueta" :class="$style.kpi">
            <span class="eyebrow">{{ Indicador.Etiqueta }}</span>
            <strong :class="$style.value">{{ Indicador.Valor }}</strong>
            <span v-if="Indicador.Detalle" class="muted">{{ Indicador.Detalle }}</span>
        </div>
    </section>

    <div :class="$style.layout">
        <section class="stack" :class="$style.main">
            <div class="chips" role="tablist" aria-label="Documentos">
                <button type="button" role="tab" class="chip" :aria-selected="Pestana_Activa === 'facturas'" :aria-pressed="Pestana_Activa === 'facturas' ? 'true' : 'false'" @click="Pestana_Activa = 'facturas'">Facturas · {{ Facturas.length }}</button>
                <button type="button" role="tab" class="chip" :aria-selected="Pestana_Activa === 'contratos'" :aria-pressed="Pestana_Activa === 'contratos' ? 'true' : 'false'" @click="Pestana_Activa = 'contratos'">Contratos y presupuestos · {{ Contratos.length }}</button>
            </div>

            <div v-if="Pestana_Activa === 'facturas'" class="table-wrap">
                <table class="table">
                    <thead><tr><th>N.º</th><th>Concepto</th><th>Emisión</th><th>Vence</th><th>Monto</th><th>Estado</th><th>Archivos</th></tr></thead>
                    <tbody>
                        <tr v-for="Factura in Facturas" :key="Factura.Id">
                            <td class="mono">{{ Factura.Numero }}</td>
                            <td>{{ Factura.Concepto }}</td>
                            <td>{{ Factura.Fecha_Emision }}</td>
                            <td>{{ Factura.Fecha_Vencimiento ?? '—' }}</td>
                            <td class="mono" :class="$style.nowrap">{{ Formatear_Moneda(Factura.Moneda) }} {{ Factura.Monto }}</td>
                            <td><Componente_Etiqueta_Estado :Estado="Factura.Estado" /></td>
                            <td :class="$style.nowrap">
                                <a v-if="Factura.Tiene_Pdf" :href="`/facturacion/factura/${Factura.Id}/pdf`" class="btn btn-secondary btn-sm">PDF</a>
                                <a v-if="Factura.Tiene_Xml" :href="`/facturacion/factura/${Factura.Id}/xml`" class="btn btn-secondary btn-sm" :class="$style.gap">XML</a>
                                <span v-if="!Factura.Tiene_Pdf && !Factura.Tiene_Xml" class="muted">—</span>
                            </td>
                        </tr>
                        <tr v-if="!Facturas.length"><td colspan="7" class="muted">Aún no hay facturas registradas.</td></tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="stack">
                <article v-for="Contrato in Contratos" :key="Contrato.Id" :class="$style.contract">
                    <div class="stack" :class="$style.cText">
                        <div class="row">
                            <span class="badge badge-navy">{{ Contrato.Codigo_Unidad ?? 'Blockfinity' }}</span>
                            <span class="badge badge-outline">{{ Contrato.Tipo === 'presupuesto' ? 'Presupuesto' : 'Contrato' }}</span>
                        </div>
                        <strong>{{ Contrato.Titulo }}</strong>
                        <span class="muted" :class="$style.small">
                            <template v-if="Contrato.Fecha_Firma">Firmado el {{ Contrato.Fecha_Firma }}</template><template v-if="Contrato.Fecha_Vencimiento"> · vigente hasta {{ Contrato.Fecha_Vencimiento }}</template><template v-if="Contrato.Monto"> · Bs {{ Contrato.Monto }}</template>
                        </span>
                        <span v-if="Contrato.Huella_Sha256" class="mono muted" :class="$style.small">Huella {{ Contrato.Huella_Sha256.slice(0, 16) }}…</span>
                    </div>
                    <a v-if="Contrato.Tiene_Archivo" :href="`/facturacion/contrato/${Contrato.Id}`" target="_blank" rel="noopener" class="btn btn-secondary btn-sm"><Componente_Icono Nombre="descargar" :Tamano="16" />Descargar</a>
                </article>
                <p v-if="!Contratos.length" class="card muted">Aún no hay contratos registrados.</p>
            </div>
            <p class="muted" :class="$style.small">Facturación electrónica emitida por Blockfinity Advisors S.R.L. en Bolivia, válida para crédito fiscal.</p>
        </section>

        <aside class="stack" :class="$style.side">
            <div class="card stack">
                <h3>Datos de facturación</h3>
                <dl :class="$style.dl">
                    <div v-for="Dato in Datos_Facturacion" :key="Dato.Etiqueta"><dt>{{ Dato.Etiqueta }}</dt><dd>{{ Dato.Valor }}</dd></div>
                </dl>
                <a v-if="Ejecutivo_Cuenta" :href="`mailto:${Ejecutivo_Cuenta.Email}?subject=${encodeURIComponent('Cambio de datos de facturación')}`" class="link-arrow">Solicitar cambio de datos →</a>
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
