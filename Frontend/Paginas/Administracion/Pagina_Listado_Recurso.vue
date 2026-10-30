<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Componente_Paginacion from '../../Componentes/Componente_Paginacion.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Estado_Vacio from '../../Componentes/Componente_Estado_Vacio.vue';

const Propiedades = defineProps({ Recurso: Object, Columnas: Array, Filas: Object, Filtro: Object, Busqueda: String });
const Texto_Busqueda = ref(Propiedades.Busqueda ?? '');
const Valor_Filtro = ref(Propiedades.Filtro?.Valor ?? '');
let Temporizador_Busqueda = null;

function Aplicar_Busqueda() {
    const Parametros = {};
    if (Texto_Busqueda.value) Parametros.buscar = Texto_Busqueda.value;
    if (Valor_Filtro.value) Parametros.filtro = Valor_Filtro.value;
    router.get(`/admin/${Propiedades.Recurso.Clave}`, Parametros, { preserveState: true, preserveScroll: true, replace: true });
}
// Busca sola mientras se escribe (espera a que se deje de teclear)
function Buscar_Mientras_Escribe() {
    clearTimeout(Temporizador_Busqueda);
    Temporizador_Busqueda = setTimeout(Aplicar_Busqueda, 350);
}
const Valor_Si_No = (Valor) => Valor === 'Sí' || Valor === 'No';
</script>

<template>
    <Head :title="Recurso.Titulo" />
    <Componente_Encabezado_Pagina :Icono="Recurso.Icono" :Titulo="Recurso.Titulo" :Introduccion="`${Filas.total} registro(s)`">
        <Link :href="`/admin/${Recurso.Clave}/crear`" class="btn btn-primary"><Componente_Icono Nombre="agregar" :Tamano="16" />Nuevo</Link>
    </Componente_Encabezado_Pagina>

    <form class="row" :class="$style.tools" @submit.prevent="Aplicar_Busqueda">
        <label :class="$style.search">
            <span class="sr-only">Buscar</span>
            <Componente_Icono Nombre="buscar" :Tamano="18" :class="$style.searchIcon" />
            <input v-model="Texto_Busqueda" type="search" class="input" :class="$style.searchInput" placeholder="Buscar…" maxlength="100" @input="Buscar_Mientras_Escribe">
        </label>
        <select v-if="Filtro" v-model="Valor_Filtro" class="select" :class="$style.filter" :aria-label="Filtro.Etiqueta" @change="Aplicar_Busqueda">
            <option value="">{{ Filtro.Etiqueta }}: todos</option>
            <option v-for="Opcion in Filtro.Opciones" :key="Opcion.Valor" :value="Opcion.Valor">{{ Opcion.Etiqueta }}</option>
        </select>
    </form>

    <Componente_Estado_Vacio v-if="!Filas.data.length" :Icono="Recurso.Icono" Texto="Sin registros">
        <Link :href="`/admin/${Recurso.Clave}/crear`" class="btn btn-secondary btn-sm"><Componente_Icono Nombre="agregar" :Tamano="14" />Crear {{ Recurso.Singular }}</Link>
    </Componente_Estado_Vacio>
    <div v-else class="table-wrap">
        <table class="table">
            <thead><tr><th v-for="Columna in Columnas" :key="Columna.Nombre">{{ Columna.Etiqueta }}</th><th><span class="sr-only">Acciones</span></th></tr></thead>
            <tbody>
                <tr v-for="Fila in Filas.data" :key="Fila.id" :class="$style.row" @click="router.visit(`/admin/${Recurso.Clave}/${Fila.id}/editar`)">
                    <td v-for="Columna in Columnas" :key="Columna.Nombre">
                        <span v-if="Valor_Si_No(Fila[Columna.Nombre])" :class="['badge', Fila[Columna.Nombre] === 'Sí' ? 'badge-ok' : 'badge-outline']">{{ Fila[Columna.Nombre] }}</span>
                        <template v-else>{{ Fila[Columna.Nombre] }}</template>
                    </td>
                    <td :class="$style.actions">
                        <Link :href="`/admin/${Recurso.Clave}/${Fila.id}/editar`" class="btn btn-secondary btn-sm" :aria-label="`Editar registro ${Fila.id}`" @click.stop>
                            <Componente_Icono Nombre="lapiz" :Tamano="15" />Editar
                        </Link>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <Componente_Paginacion :Enlaces="Filas.links" />
</template>

<style module>
.tools { gap: 10px; }
.search { position: relative; flex: 1 1 280px; max-width: 420px; }
.searchIcon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
.searchInput { padding-left: 42px; }
.filter { width: auto; max-width: 280px; }
.row { cursor: pointer; }
.actions { text-align: right; }
</style>
