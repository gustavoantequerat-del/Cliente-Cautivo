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
                <span :class="[$style.seal, $style.sealOk]"><Componente_Icono Nombre="escudo" :Tamano="34" /></span>
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
                <span :class="[$style.seal, $style.sealBad]"><Componente_Icono Nombre="alerta" :Tamano="34" /></span>
                <span class="badge badge-danger" :class="$style.state">Credencial revocada</span>
                <p>Esta credencial existió pero ya no está activa.</p>
            </template>
            <template v-else>
                <span :class="[$style.seal, $style.sealBad]"><Componente_Icono Nombre="buscar" :Tamano="34" /></span>
                <span class="badge badge-danger" :class="$style.state">No encontrada</span>
                <p>El código no corresponde a ninguna credencial emitida por Blockfinity.</p>
            </template>
            <p :class="$style.foot">Verificación emitida por Blockfinity Advisors S.R.L.</p>
        </div>
    </div>
</template>

<style module>
.page { background: var(--gradient-hero); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px; }
.card { width: 100%; max-width: 440px; background: #fff; border-radius: 24px; padding: 32px; display: flex; flex-direction: column; gap: 18px; box-shadow: 0 30px 70px rgba(0, 0, 0, 0.35); animation: aparecer-escala 0.5s var(--ease) both; }
.seal { align-self: center; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; animation: aparecer-escala 0.6s var(--ease) 0.2s both; }
.sealOk { background: var(--ok-bg); color: var(--ok-fg); box-shadow: 0 0 0 10px rgba(228, 242, 234, 0.5); }
.sealBad { background: var(--danger-bg); color: var(--danger-fg); }
.state { align-self: center; font-size: 14px; padding: 6px 12px; }
.name { font-size: 28px; }
.list { margin: 0; display: flex; flex-direction: column; gap: 10px; }
.list div { display: flex; justify-content: space-between; gap: 12px; border-bottom: 1px solid #E8EBEF; padding-bottom: 8px; }
.list dt { color: var(--muted); font-size: 14px; }
.list dd { margin: 0; font-weight: 700; text-align: right; }
.foot { font-size: 12px; color: var(--muted); }
</style>
