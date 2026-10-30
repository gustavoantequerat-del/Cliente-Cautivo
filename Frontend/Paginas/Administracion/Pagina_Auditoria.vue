<script setup>
import { Head } from '@inertiajs/vue3';
import Componente_Paginacion from '../../Componentes/Componente_Paginacion.vue';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';

// Color de la insignia según el tipo de acción
const Clase_Por_Accion = (Accion) => (Accion.startsWith('inicio_sesion.') ? 'badge-danger' : Accion.startsWith('inicio_sesion') ? 'badge-blue' : Accion.startsWith('cms.') ? 'badge-navy' : Accion.includes('aprobar') ? 'badge-ok' : 'badge-outline');

defineProps({ Registros: Object });
</script>

<template>
    <Head title="Auditoría" />
    <Componente_Encabezado_Pagina Icono="escudo" Titulo="Auditoría" Introduccion="Se conservan 18 meses." />
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Detalle</th><th>IP</th></tr></thead>
            <tbody>
                <tr v-for="Registro in Registros.data" :key="Registro.Id">
                    <td class="mono">{{ Registro.Fecha }}</td>
                    <td>{{ Registro.Usuario }}</td>
                    <td><span :class="['badge', Clase_Por_Accion(Registro.Accion)]">{{ Registro.Accion }}</span></td>
                    <td>{{ Registro.Descripcion }}<div v-if="Registro.Huella_Sha256" class="mono muted" :class="$style.hash">{{ Registro.Huella_Sha256 }}</div></td>
                    <td class="mono">{{ Registro.Ip }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <Componente_Paginacion :Enlaces="Registros.links" />
</template>

<style module>
.hash { font-size: 11px; word-break: break-all; }
</style>
