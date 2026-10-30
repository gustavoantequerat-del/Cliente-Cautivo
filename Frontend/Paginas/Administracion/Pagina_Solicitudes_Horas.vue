<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Componente_Etiqueta_Estado from '../../Componentes/Componente_Etiqueta_Estado.vue';
import Componente_Paginacion from '../../Componentes/Componente_Paginacion.vue';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Estado_Vacio from '../../Componentes/Componente_Estado_Vacio.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

const Propiedades = defineProps({ Solicitudes: Object, Estados: Object, Filtro_Estado: String });

// Un borrador editable por solicitud: estado, horas a descontar y respuesta
const Borradores = reactive(Object.fromEntries(Propiedades.Solicitudes.data.map((Solicitud) => [Solicitud.Id, {
    estado: Solicitud.Estado,
    horas_cobradas: Solicitud.Horas_Cobradas ?? Solicitud.Horas_Estimadas,
    respuesta: Solicitud.Respuesta ?? '',
}])));
// Horas a descontar: solo números con hasta 2 decimales
function Filtrar_Horas(Evento, Id_Solicitud) {
    const [Parte_Entera, ...Decimales] = Evento.target.value.replace(',', '.').replace(/[^\d.]/g, '').split('.');
    const Valor_Limpio = Parte_Entera.slice(0, 3) + (Decimales.length ? '.' + Decimales.join('').slice(0, 2) : '');
    Evento.target.value = Valor_Limpio;
    Borradores[Id_Solicitud].horas_cobradas = Valor_Limpio;
}
const Guardar_Solicitud = (Id_Solicitud) => router.put(`/admin/solicitudes-horas/${Id_Solicitud}`, Borradores[Id_Solicitud], { preserveScroll: true });
</script>

<template>
    <Head title="Solicitudes de horas" />
    <Componente_Encabezado_Pagina Icono="reloj" Titulo="Solicitudes de horas" />
    <div class="chips">
        <Link href="/admin/solicitudes-horas" class="chip" :aria-current="Filtro_Estado === 'abiertas' ? 'true' : undefined">Abiertas</Link>
        <Link href="/admin/solicitudes-horas?estado=todas" class="chip" :aria-current="Filtro_Estado === 'todas' ? 'true' : undefined">Todas</Link>
    </div>
    <Componente_Estado_Vacio v-if="!Solicitudes.data.length" Icono="visto" Texto="¡Todo atendido! No hay solicitudes." />
    <div class="stack escalonado">
    <article v-for="Solicitud in Solicitudes.data" :key="Solicitud.Id" class="card stack" :class="Solicitud.Modalidad === 'emergencia' && $style.urgent">
        <div class="row between">
            <div class="row">
                <span :class="['badge', Solicitud.Modalidad === 'emergencia' ? 'badge-danger' : 'badge-blue']"><Componente_Icono :Nombre="Solicitud.Modalidad === 'emergencia' ? 'rayo' : 'calendario'" :Tamano="13" />{{ Solicitud.Modalidad }}</span>
                <strong>{{ Solicitud.Institucion }}</strong>
                <span class="muted">{{ Solicitud.Solicitante }} · <a :href="`mailto:${Solicitud.Email}`">{{ Solicitud.Email }}</a> · {{ Solicitud.Fecha }}</span>
            </div>
            <Componente_Etiqueta_Estado :Estado="Solicitud.Estado" />
        </div>
        <div class="row">
            <span class="badge badge-outline">{{ Solicitud.Especialidad }}</span>
            <span class="badge badge-outline"><Componente_Icono Nombre="reloj" :Tamano="13" />{{ Solicitud.Horas_Estimadas }} h estimadas</span>
            <span class="badge badge-outline"><Componente_Icono Nombre="dinero" :Tamano="13" />Saldo {{ Solicitud.Saldo_Institucion }} h</span>
        </div>
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
                <input :id="`horas-${Solicitud.Id}`" :value="Borradores[Solicitud.Id].horas_cobradas" type="text" inputmode="decimal" maxlength="6" class="input"
                       :disabled="Solicitud.Estado === 'atendida'" @input="Filtrar_Horas($event, Solicitud.Id)">
                <span class="help">Se descuentan al marcar «Atendida».</span>
            </div>
            <div class="field" :class="$style.wide">
                <label :for="`respuesta-${Solicitud.Id}`">Respuesta visible para el cliente</label>
                <textarea :id="`respuesta-${Solicitud.Id}`" v-model="Borradores[Solicitud.Id].respuesta" class="textarea" maxlength="3000" />
            </div>
            <button type="submit" class="btn btn-primary"><Componente_Icono Nombre="visto" :Tamano="16" />Guardar</button>
        </form>
    </article>
    </div>
    <Componente_Paginacion :Enlaces="Solicitudes.links" />
</template>

<style module>
.desc { white-space: pre-line; background: var(--surface-2); border-radius: 10px; padding: 12px; font-size: 14px; }
.form { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr)); align-items: end; }
.wide { grid-column: 1 / -1; }
.urgent { border-left: 4px solid #D0574B; }
</style>
