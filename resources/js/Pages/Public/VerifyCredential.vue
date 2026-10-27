<script setup>
import { Head } from '@inertiajs/vue3';
import BrandMark from '../../Components/BrandMark.vue';
import Icon from '../../Components/Icon.vue';

defineProps({ credential: Object });
</script>

<template>
    <Head title="Verificación de credencial" />
    <div :class="$style.page">
        <div :class="$style.card">
            <BrandMark dark />
            <template v-if="credential && credential.valid">
                <span class="badge badge-ok" :class="$style.state"><Icon name="check" :size="16" />Credencial válida</span>
                <h1 :class="$style.name">{{ credential.name }}</h1>
                <dl :class="$style.list">
                    <div><dt>Institución</dt><dd>{{ credential.institution }}</dd></div>
                    <div v-if="credential.area"><dt>Área</dt><dd>{{ credential.area }}</dd></div>
                    <div><dt>N.º de credencial</dt><dd class="mono">#{{ credential.number }}</dd></div>
                    <div><dt>Emitida</dt><dd class="mono">{{ credential.issuedAt }}</dd></div>
                </dl>
            </template>
            <template v-else-if="credential">
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
