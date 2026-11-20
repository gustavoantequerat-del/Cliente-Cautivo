<script setup>
/* Avatar plano en SVG. El dibujo lo arma Utilidades/Dibujo_Avatar.js (solo con estilos y colores validados). */
import { computed } from 'vue';
import { Construir_Svg_Avatar } from '../Utilidades/Dibujo_Avatar.js';

const Propiedades = defineProps({
    Objetos: { type: Array, default: () => [] },
    Recorte: { type: String, default: 'completo' }, // completo | busto
});
const Svg_Avatar = computed(() => Construir_Svg_Avatar(Propiedades.Objetos, Propiedades.Recorte));
</script>

<template>
    <!-- eslint-disable-next-line vue/no-v-html -->
    <span :class="$style.avatar" v-html="Svg_Avatar" />
</template>

<style module>
.avatar { display: block; width: 100%; height: 100%; }
.avatar svg { display: block; width: 100%; height: 100%; }
</style>
