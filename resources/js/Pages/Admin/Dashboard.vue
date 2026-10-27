<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({ stats: Array, groups: Object });
</script>

<template>
    <Head title="CMS" />
    <h1>Resumen</h1>
    <section :class="$style.stats">
        <div v-for="s in stats" :key="s.label" class="card">
            <span class="eyebrow">{{ s.label }}</span>
            <strong :class="$style.value">{{ s.value }}</strong>
        </div>
    </section>
    <section class="grid-3">
        <div v-for="(items, group) in groups" :key="group" class="card stack">
            <h2>{{ group }}</h2>
            <Link v-for="i in items" :key="i.key" :href="`/admin/${i.key}`" :class="$style.item">
                <span>{{ i.label }}</span><span class="badge badge-outline">{{ i.count }}</span>
            </Link>
        </div>
    </section>
</template>

<style module>
.stats { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 200px), 1fr)); }
.value { display: block; font-size: 32px; font-weight: 800; color: var(--navy-950); margin-top: 6px; }
.item { display: flex; justify-content: space-between; align-items: center; min-height: 40px; text-decoration: none; font-weight: 600; border-bottom: 1px solid #E8EBEF; }
</style>
