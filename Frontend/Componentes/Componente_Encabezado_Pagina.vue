<script setup>
defineProps({
    Titulo: { type: String, required: true },
    Introduccion: { type: String, default: '' },
    Antetitulo: { type: String, default: '' },
});
</script>

<template>
    <header :class="$style.header">
        <div :class="$style.text">
            <span v-if="Antetitulo" class="eyebrow">{{ Antetitulo }}</span>
            <h1>{{ Titulo }}</h1>
            <p v-if="Introduccion" :class="$style.intro">{{ Introduccion }}</p>
        </div>
    </header>
</template>

<style module>
.header { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; }
.text { display: flex; flex-direction: column; gap: 8px; max-width: 760px; }
.intro { font-size: 16px; color: var(--text-2); line-height: 1.6; }
</style>
