<script setup>
import { Head } from '@inertiajs/vue3';
import Componente_Marca_Blockfinity from '../../Componentes/Componente_Marca_Blockfinity.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

defineProps({ Credencial: Object });
</script>

<template>
    <Head title="Verificación de credencial" />
    <div :class="$style.page">
        <div :class="$style.card">
            <Componente_Marca_Blockfinity Fondo_Claro />
            <template v-if="Credencial && Credencial.Es_Valida">
                <span class="badge badge-ok" :class="$style.state"><Componente_Icono Nombre="visto" :Tamano="16" />Credencial válida</span>
                <h1 :class="$style.name">{{ Credencial.Nombre }}</h1>
                <dl :class="$style.list">
                    <div><dt>Institución</dt><dd>{{ Credencial.Institucion }}</dd></div>
                    <div v-if="Credencial.Area"><dt>Área</dt><dd>{{ Credencial.Area }}</dd></div>
                    <div><dt>N.º de credencial</dt><dd class="mono">#{{ Credencial.Numero }}</dd></div>
                    <div><dt>Emitida</dt><dd class="mono">{{ Credencial.Fecha_Emision }}</dd></div>
                </dl>
            </template>
            <template v-else-if="Credencial">
                <span class="badge badge-danger" :class="$style.state">Credencial revocada</span>
                <p>Esta credencial existió pero ya no está activa.</p>
            </template>
            <template v-else>
                <span class="badge badge-danger" :class="$style.state">No encontrada</span>
                <p>El código no corresponde a ninguna credencial emitida por Blockfinity.</p>
            </template>
            <p :class="$style.foot">Verificación emitida por Blockfinity Advisors S.R.L.</p>
        </div>
    </div>
</template>

<style module>
.page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px; }
.card { width: 100%; max-width: 440px; background: #fff; border: 1px solid var(--line); border-radius: 20px; padding: 32px; display: flex; flex-direction: column; gap: 18px; }
.state { align-self: flex-start; font-size: 14px; padding: 6px 12px; }
.name { font-size: 28px; }
.list { margin: 0; display: flex; flex-direction: column; gap: 10px; }
.list div { display: flex; justify-content: space-between; gap: 12px; border-bottom: 1px solid #E8EBEF; padding-bottom: 8px; }
.list dt { color: var(--muted); font-size: 14px; }
.list dd { margin: 0; font-weight: 700; text-align: right; }
.foot { font-size: 12px; color: var(--muted); }
</style>
