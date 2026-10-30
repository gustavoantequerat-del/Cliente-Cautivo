<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Componente_Paginacion from '../../Componentes/Componente_Paginacion.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

const Propiedades = defineProps({ Recurso: Object, Columnas: Array, Filas: Object, Filtro: Object, Busqueda: String });
const Texto_Busqueda = ref(Propiedades.Busqueda ?? '');
const Valor_Filtro = ref(Propiedades.Filtro?.Valor ?? '');

function Aplicar_Busqueda() {
    const Parametros = {};
    if (Texto_Busqueda.value) Parametros.buscar = Texto_Busqueda.value;
    if (Valor_Filtro.value) Parametros.filtro = Valor_Filtro.value;
    router.get(`/admin/${Propiedades.Recurso.Clave}`, Parametros, { preserveState: true });
}
</script>

<template>
    <Head :title="Recurso.Titulo" />
    <div class="row between">
        <h1>{{ Recurso.Titulo }}</h1>
        <Link :href="`/admin/${Recurso.Clave}/crear`" class="btn btn-primary"><Componente_Icono Nombre="agregar" :Tamano="16" />Nuevo/a {{ Recurso.Singular }}</Link>
    </div>
    <form class="row" @submit.prevent="Aplicar_Busqueda">
        <label class="sr-only" for="buscar">Buscar</label>
        <input id="buscar" v-model="Texto_Busqueda" type="search" class="input" :class="$style.search" placeholder="Buscar…">
        <select v-if="Filtro" v-model="Valor_Filtro" class="select" :class="$style.filter" :aria-label="Filtro.Etiqueta" @change="Aplicar_Busqueda">
            <option value="">{{ Filtro.Etiqueta }}: todos</option>
            <option v-for="Opcion in Filtro.Opciones" :key="Opcion.Valor" :value="Opcion.Valor">{{ Opcion.Etiqueta }}</option>
        </select>
        <button type="submit" class="btn btn-secondary">Buscar</button>
    </form>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th v-for="Columna in Columnas" :key="Columna.Nombre">{{ Columna.Etiqueta }}</th><th><span class="sr-only">Acciones</span></th></tr></thead>
            <tbody>
                <tr v-for="Fila in Filas.data" :key="Fila.id">
                    <td v-for="Columna in Columnas" :key="Columna.Nombre">{{ Fila[Columna.Nombre] }}</td>
                    <td><Link :href="`/admin/${Recurso.Clave}/${Fila.id}/editar`" class="btn btn-secondary btn-sm">Editar</Link></td>
                </tr>
                <tr v-if="!Filas.data.length"><td :colspan="Columnas.length + 1" class="muted">Sin registros.</td></tr>
            </tbody>
        </table>
    </div>
    <Componente_Paginacion :Enlaces="Filas.links" />
</template>

<style module>
.search { max-width: 320px; }
.filter { width: auto; max-width: 280px; }
</style>
