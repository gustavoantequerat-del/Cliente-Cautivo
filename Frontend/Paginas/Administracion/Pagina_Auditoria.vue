<script setup>
import { Head } from '@inertiajs/vue3';
import Componente_Paginacion from '../../Componentes/Componente_Paginacion.vue';

defineProps({ Registros: Object });
</script>

<template>
    <Head title="Auditoría" />
    <h1>Auditoría</h1>
    <p class="muted">Inicios de sesión, descargas, aprobaciones y cambios en el CMS. Se conservan 18 meses.</p>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Detalle</th><th>IP</th></tr></thead>
            <tbody>
                <tr v-for="Registro in Registros.data" :key="Registro.Id">
                    <td class="mono">{{ Registro.Fecha }}</td>
                    <td>{{ Registro.Usuario }}</td>
                    <td><span class="badge badge-outline">{{ Registro.Accion }}</span></td>
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
