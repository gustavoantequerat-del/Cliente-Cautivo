<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({ Indicadores: Array, Recursos_Por_Grupo: Object });
</script>

<template>
    <Head title="CMS" />
    <h1>Resumen</h1>
    <section :class="$style.stats">
        <div v-for="Indicador in Indicadores" :key="Indicador.Etiqueta" class="card">
            <span class="eyebrow">{{ Indicador.Etiqueta }}</span>
            <strong :class="$style.value">{{ Indicador.Valor }}</strong>
        </div>
    </section>
    <section class="grid-3">
        <div v-for="(Recursos, Nombre_Grupo) in Recursos_Por_Grupo" :key="Nombre_Grupo" class="card stack">
            <h2>{{ Nombre_Grupo }}</h2>
            <Link v-for="Recurso in Recursos" :key="Recurso.Clave" :href="`/admin/${Recurso.Clave}`" :class="$style.item">
                <span>{{ Recurso.Titulo }}</span><span class="badge badge-outline">{{ Recurso.Cantidad }}</span>
            </Link>
        </div>
    </section>
</template>

<style module>
.stats { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 200px), 1fr)); }
.value { display: block; font-size: 32px; font-weight: 800; color: var(--navy-950); margin-top: 6px; }
.item { display: flex; justify-content: space-between; align-items: center; min-height: 40px; text-decoration: none; font-weight: 600; border-bottom: 1px solid #E8EBEF; }
</style>
