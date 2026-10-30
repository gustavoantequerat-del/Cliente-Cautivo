<script setup>
import Componente_Icono from './Componente_Icono.vue';

defineProps({
    Icono: { type: String, default: 'info' },
    Texto: { type: String, required: true },
});
</script>

<template>
    <div class="vacio animar-entrada">
        <span class="burbuja"><Componente_Icono :Nombre="Icono" :Tamano="26" /></span>
        <strong>{{ Texto }}</strong>
        <slot />
    </div>
</template>
