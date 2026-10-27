<script setup>
defineProps({
    title: { type: String, required: true },
    intro: { type: String, default: '' },
    eyebrow: { type: String, default: '' },
});
</script>

<template>
    <header :class="$style.header">
        <div :class="$style.text">
            <span v-if="eyebrow" class="eyebrow">{{ eyebrow }}</span>
            <h1>{{ title }}</h1>
            <p v-if="intro" :class="$style.intro">{{ intro }}</p>
        </div>
        <div v-if="$slots.actions" :class="$style.actions"><slot name="actions" /></div>
    </header>
</template>

<style module>
.header { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; }
.text { display: flex; flex-direction: column; gap: 8px; max-width: 760px; }
.intro { font-size: 16px; color: var(--text-2); line-height: 1.6; }
.actions { display: flex; gap: 10px; flex-wrap: wrap; }
</style>
