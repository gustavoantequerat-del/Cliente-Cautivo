<script setup>
import { ref, watchEffect } from 'vue';
import QRCode from 'qrcode';

const props = defineProps({
    value: { type: String, required: true },
    size: { type: Number, default: 132 },
    label: { type: String, default: 'Código QR de verificación' },
});
const src = ref('');

watchEffect(async () => {
    src.value = await QRCode.toDataURL(props.value, { margin: 1, width: props.size * 2, color: { dark: '#0A1628', light: '#FFFFFF' } });
});
</script>

<template>
    <img v-if="src" :src="src" :alt="label" :width="size" :height="size" :class="$style.qr">
</template>

<style module>
.qr { display: block; border-radius: 8px; background: #fff; }
</style>
