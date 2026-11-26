<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Componente_Visor_Pdf from '../../Componentes/Componente_Visor_Pdf.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

defineProps({ Reporte: Object, Texto_Marca_Agua: String });
</script>

<template>
    <Head :title="Reporte.Titulo" />
    <Link href="/research" :class="$style.back">← Research</Link>

    <header :class="$style.head" class="card">
        <div class="row">
            <span class="badge badge-blue">{{ Reporte.Tema }}</span>
            <span v-if="Reporte.Es_Exclusivo" class="badge badge-navy">Exclusivo</span>
            <span v-if="Reporte.Fecha" class="muted" :class="$style.inline"><Componente_Icono Nombre="calendario" :Tamano="14" />{{ Reporte.Fecha }}</span>
            <span v-if="Reporte.Minutos_Lectura" class="muted" :class="$style.inline"><Componente_Icono Nombre="reloj" :Tamano="14" />{{ Reporte.Minutos_Lectura }} min</span>
        </div>
        <h1 :class="$style.title">{{ Reporte.Titulo }}</h1>
        <p v-if="Reporte.Resumen" class="muted">{{ Reporte.Resumen }}</p>
    </header>

    <Componente_Visor_Pdf v-if="Reporte.Tiene_Archivo" :Url_Documento="Reporte.Url_Documento" :Texto_Marca_Agua="Texto_Marca_Agua" />
    <p v-else class="vacio">Este reporte aún no tiene documento.</p>
</template>

<style module>
.back { align-self: flex-start; font-weight: 700; font-size: 14px; text-decoration: none; padding: 6px 12px; border-radius: 999px; background: var(--surface); border: 1px solid var(--line); }
.head { display: flex; flex-direction: column; gap: 10px; max-width: 900px; width: 100%; align-self: center; }
.title { font-size: 26px; }
.inline { display: inline-flex; align-items: center; gap: 5px; font-size: 13px; }
</style>
