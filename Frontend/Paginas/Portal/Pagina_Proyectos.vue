<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Etiqueta_Estado from '../../Componentes/Componente_Etiqueta_Estado.vue';

defineProps({ Lista_Proyectos: Array });
</script>

<template>
    <Head title="Proyectos" />
    <Componente_Encabezado_Pagina Titulo="Proyectos" Introduccion="Avance, fases, entregables y bitácora de cada proyecto que Blockfinity ejecuta para su institución." />
    <p v-if="!Lista_Proyectos.length" class="card muted">Aún no hay proyectos registrados.</p>
    <div class="grid-2">
        <Link v-for="Proyecto in Lista_Proyectos" :key="Proyecto.Id" :href="`/proyectos/${Proyecto.Id}`" :class="$style.card">
            <div class="row between">
                <span class="badge badge-navy">{{ Proyecto.Codigo_Unidad ?? 'Blockfinity' }}</span>
                <Componente_Etiqueta_Estado :Estado="Proyecto.Estado" />
            </div>
            <h2 :class="$style.name">{{ Proyecto.Nombre }}</h2>
            <span v-if="Proyecto.Cantidad_Fases" class="muted">
                <template v-if="Proyecto.Numero_Fase_Actual">Fase {{ Proyecto.Numero_Fase_Actual }} de {{ Proyecto.Cantidad_Fases }} · {{ Proyecto.Fase_Actual }}</template>
                <template v-else>{{ Proyecto.Cantidad_Fases }} fases</template>
            </span>
            <div :class="$style.bar"><span :style="{ width: Proyecto.Avance + '%' }" /></div>
            <div class="row between">
                <span class="muted">{{ Proyecto.Proximo_Hito ? 'Próximo hito: ' + Proyecto.Proximo_Hito : '' }}</span>
                <span class="mono">{{ Proyecto.Avance }}%</span>
            </div>
        </Link>
    </div>
</template>

<style module>
.card {
  background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 22px;
  display: flex; flex-direction: column; gap: 12px; text-decoration: none; color: var(--ink);
}
.card:hover { border-color: var(--navy-700); color: var(--ink); }
.name { font-size: 19px; }
.bar { height: 8px; border-radius: 99px; background: var(--blue-100); overflow: hidden; }
.bar span { display: block; height: 100%; background: var(--navy-700); }
</style>
