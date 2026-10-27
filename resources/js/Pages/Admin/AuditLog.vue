<script setup>
import { Head } from '@inertiajs/vue3';
import Pagination from '../../Components/Pagination.vue';

defineProps({ logs: Object, action: String });
</script>

<template>
    <Head title="Auditoría" />
    <h1>Auditoría</h1>
    <p class="muted">Inicios de sesión, descargas, aprobaciones y cambios en el CMS. Se conservan 18 meses.</p>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Detalle</th><th>IP</th></tr></thead>
            <tbody>
                <tr v-for="l in logs.data" :key="l.id">
                    <td class="mono">{{ l.when }}</td>
                    <td>{{ l.user }}</td>
                    <td><span class="badge badge-outline">{{ l.action }}</span></td>
                    <td>{{ l.description }}<div v-if="l.meta?.sha256" class="mono muted" :class="$style.hash">{{ l.meta.sha256 }}</div></td>
                    <td class="mono">{{ l.ip }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <Pagination :links="logs.links" />
</template>

<style module>
.hash { font-size: 11px; word-break: break-all; }
</style>
