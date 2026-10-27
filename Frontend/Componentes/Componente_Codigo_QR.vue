<script setup>
import { ref, watchEffect } from 'vue';
import QRCode from 'qrcode';

const Propiedades = defineProps({
    Valor: { type: String, required: true },
    Tamano: { type: Number, default: 132 },
    Texto_Alternativo: { type: String, default: 'Código QR de verificación' },
});
const Imagen_QR = ref('');

watchEffect(async () => {
    Imagen_QR.value = await QRCode.toDataURL(Propiedades.Valor, { margin: 1, width: Propiedades.Tamano * 2, color: { dark: '#0A1628', light: '#FFFFFF' } });
});
</script>

<template>
    <img v-if="Imagen_QR" :src="Imagen_QR" :alt="Texto_Alternativo" :width="Tamano" :height="Tamano" :class="$style.qr">
</template>

<style module>
.qr { display: block; border-radius: 8px; background: #fff; }
</style>
