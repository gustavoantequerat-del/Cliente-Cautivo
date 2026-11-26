<script setup>
/*
 * Logo oficial de Blockfinity Advisors (Backend/public/marca). El original tiene el texto en blanco:
 * sobre fondos claros se usa la versión con texto azul marino. Compacta = solo el símbolo (junto al logo de la institución).
 */
defineProps({ Fondo_Claro: { type: Boolean, default: false }, Compacta: { type: Boolean, default: false } });
</script>

<template>
    <img v-if="Compacta" src="/marca/blockfinity-simbolo.png" alt="Blockfinity Advisors" width="208" height="100" :class="[$style.logo, $style.compact]">
    <img v-else :src="Fondo_Claro ? '/marca/blockfinity-fondo-claro.png' : '/marca/blockfinity-fondo-oscuro.png'"
         alt="Blockfinity Advisors" width="658" height="136" :class="$style.logo">
</template>

<style module>
.logo { display: block; flex: none; align-self: flex-start; height: 38px; width: auto; max-width: 100%; object-fit: contain; }
.compact { height: 26px; }
</style>
