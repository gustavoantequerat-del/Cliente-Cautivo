<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Etiqueta_Estado from '../../Componentes/Componente_Etiqueta_Estado.vue';
import Componente_Estado_Vacio from '../../Componentes/Componente_Estado_Vacio.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

defineProps({ Lista_Proyectos: Array });
const Es_Administrador_Delegado = usePage().props.Usuario_Actual.Es_Administrador_Delegado;
</script>

<template>
    <Head title="Proyectos" />
    <Componente_Encabezado_Pagina Icono="proyectos" Titulo="Proyectos" Introduccion="Avance, fases y entregables de cada proyecto." />
    <Componente_Estado_Vacio v-if="!Lista_Proyectos.length" Icono="proyectos" :Texto="Es_Administrador_Delegado ? 'Aún no hay proyectos registrados' : 'Aún no participa en proyectos'" />
    <div class="grid-2 escalonado">
        <Link v-for="Proyecto in Lista_Proyectos" :key="Proyecto.Id" :href="`/proyectos/${Proyecto.Id}`" :class="$style.card" class="elevable">
            <div class="row between">
                <span class="badge badge-navy">{{ Proyecto.Codigo_Unidad ?? 'Blockfinity' }}</span>
                <Componente_Etiqueta_Estado :Estado="Proyecto.Estado" />
            </div>
            <h2 :class="$style.name">{{ Proyecto.Nombre }}</h2>
            <div :class="$style.progress">
                <div class="barra" :class="$style.bar"><span :style="{ width: Proyecto.Avance + '%' }" /></div>
                <strong>{{ Proyecto.Avance }}%</strong>
            </div>
            <div :class="$style.meta">
                <span v-if="Proyecto.Cantidad_Fases"><Componente_Icono Nombre="lista" :Tamano="15" />
                    <template v-if="Proyecto.Numero_Fase_Actual">Fase {{ Proyecto.Numero_Fase_Actual }}/{{ Proyecto.Cantidad_Fases }} · {{ Proyecto.Fase_Actual }}</template>
                    <template v-else>{{ Proyecto.Cantidad_Fases }} fases</template>
                </span>
                <span v-if="Proyecto.Proximo_Hito"><Componente_Icono Nombre="calendario" :Tamano="15" />{{ Proyecto.Proximo_Hito }}</span>
            </div>
        </Link>
    </div>
</template>

<style module>
.card {
  background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 22px; box-shadow: var(--shadow);
  display: flex; flex-direction: column; gap: 14px; text-decoration: none; color: var(--ink);
}
.card:hover { color: var(--ink); }
.name { font-size: 19px; }
.progress { display: flex; align-items: center; gap: 12px; }
.progress strong { font-size: 15px; color: var(--navy-700); }
.bar { flex: 1; }
.meta { display: flex; flex-wrap: wrap; gap: 8px 16px; font-size: 13px; color: var(--muted); }
.meta span { display: inline-flex; align-items: center; gap: 6px; }
</style>
