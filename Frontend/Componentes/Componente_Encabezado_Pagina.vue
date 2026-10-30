<script setup>
import Componente_Icono from './Componente_Icono.vue';

defineProps({
    Titulo: { type: String, required: true },
    Introduccion: { type: String, default: '' },
    Antetitulo: { type: String, default: '' },
    Icono: { type: String, default: '' },
});
</script>

<template>
    <header :class="$style.header">
        <span v-if="Icono" class="burbuja" :class="$style.icon"><Componente_Icono :Nombre="Icono" :Tamano="24" /></span>
        <div :class="$style.text">
            <span v-if="Antetitulo" class="eyebrow">{{ Antetitulo }}</span>
            <h1>{{ Titulo }}</h1>
            <p v-if="Introduccion" :class="$style.intro">{{ Introduccion }}</p>
        </div>
        <div v-if="$slots.default" :class="$style.actions"><slot /></div>
    </header>
</template>

<style module>
.header { display: flex; flex-wrap: wrap; align-items: center; gap: 16px; }
.icon { width: 52px; height: 52px; border-radius: 16px; background: var(--gradient-primary); color: #fff; box-shadow: 0 8px 20px rgba(15, 34, 64, 0.25); }
.text { display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 220px; }
.intro { font-size: 15px; color: var(--muted); }
.actions { display: flex; gap: 10px; flex-wrap: wrap; }
</style>
