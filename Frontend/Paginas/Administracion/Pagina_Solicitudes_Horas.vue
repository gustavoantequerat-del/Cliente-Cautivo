<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Componente_Etiqueta_Estado from '../../Componentes/Componente_Etiqueta_Estado.vue';
import Componente_Paginacion from '../../Componentes/Componente_Paginacion.vue';

const Propiedades = defineProps({ Solicitudes: Object, Estados: Object, Filtro_Estado: String });

// Un borrador editable por solicitud: estado, horas a descontar y respuesta
const Borradores = reactive(Object.fromEntries(Propiedades.Solicitudes.data.map((Solicitud) => [Solicitud.Id, {
    estado: Solicitud.Estado,
    horas_cobradas: Solicitud.Horas_Cobradas ?? Solicitud.Horas_Estimadas,
    respuesta: Solicitud.Respuesta ?? '',
}])));
const Guardar_Solicitud = (Id_Solicitud) => router.put(`/admin/solicitudes-horas/${Id_Solicitud}`, Borradores[Id_Solicitud], { preserveScroll: true });
</script>

<template>
    <Head title="Solicitudes de horas" />
    <h1>Solicitudes de horas</h1>
    <div class="chips">
        <Link href="/admin/solicitudes-horas" class="chip" :aria-current="Filtro_Estado === 'abiertas' ? 'true' : undefined">Abiertas</Link>
        <Link href="/admin/solicitudes-horas?estado=todas" class="chip" :aria-current="Filtro_Estado === 'todas' ? 'true' : undefined">Todas</Link>
    </div>
    <p v-if="!Solicitudes.data.length" class="card muted">No hay solicitudes.</p>
    <article v-for="Solicitud in Solicitudes.data" :key="Solicitud.Id" class="card stack">
        <div class="row between">
            <div class="row">
                <span :class="['badge', Solicitud.Modalidad === 'emergencia' ? 'badge-danger' : 'badge-blue']">{{ Solicitud.Modalidad }}</span>
                <strong>{{ Solicitud.Institucion }}</strong>
                <span class="muted">{{ Solicitud.Solicitante }} · <a :href="`mailto:${Solicitud.Email}`">{{ Solicitud.Email }}</a> · {{ Solicitud.Fecha }}</span>
            </div>
            <Componente_Etiqueta_Estado :Estado="Solicitud.Estado" />
        </div>
        <p><strong>{{ Solicitud.Especialidad }}</strong> · estimadas {{ Solicitud.Horas_Estimadas }} h · saldo de la institución {{ Solicitud.Saldo_Institucion }} h</p>
        <p :class="$style.desc">{{ Solicitud.Descripcion }}</p>
        <form :class="$style.form" @submit.prevent="Guardar_Solicitud(Solicitud.Id)">
            <div class="field">
                <label :for="`estado-${Solicitud.Id}`">Estado</label>
                <select :id="`estado-${Solicitud.Id}`" v-model="Borradores[Solicitud.Id].estado" class="select">
                    <option v-for="(Nombre_Estado, Clave_Estado) in Estados" :key="Clave_Estado" :value="Clave_Estado">{{ Nombre_Estado }}</option>
                </select>
            </div>
            <div class="field">
                <label :for="`horas-${Solicitud.Id}`">Horas a descontar</label>
                <input :id="`horas-${Solicitud.Id}`" v-model="Borradores[Solicitud.Id].horas_cobradas" type="number" step="0.25" min="0" class="input" :disabled="Solicitud.Estado === 'atendida'">
                <span class="help">Se descuentan al marcar «Atendida».</span>
            </div>
            <div class="field" :class="$style.wide">
                <label :for="`respuesta-${Solicitud.Id}`">Respuesta visible para el cliente</label>
                <textarea :id="`respuesta-${Solicitud.Id}`" v-model="Borradores[Solicitud.Id].respuesta" class="textarea" />
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </article>
    <Componente_Paginacion :Enlaces="Solicitudes.links" />
</template>

<style module>
.desc { white-space: pre-line; background: var(--surface-2); border-radius: 10px; padding: 12px; font-size: 14px; }
.form { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr)); align-items: end; }
.wide { grid-column: 1 / -1; }
</style>
