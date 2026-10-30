<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Componente_Etiqueta_Estado from '../../Componentes/Componente_Etiqueta_Estado.vue';
import Componente_Panel_Aprobacion from '../../Componentes/Componente_Panel_Aprobacion.vue';
import Componente_Boton_Descargar_Entregable from '../../Componentes/Componente_Boton_Descargar_Entregable.vue';
import Componente_Estado_Vacio from '../../Componentes/Componente_Estado_Vacio.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

const Propiedades = defineProps({ Proyecto: Object, Entregables: Array });
const Entregables_Pendientes = computed(() => Propiedades.Entregables.filter((Entregable) => Entregable.Estado === 'pendiente_aprobacion'));
const Nombres_Estado_Fase = { completada: 'Completada', en_curso: 'En curso', pendiente: 'Pendiente' };
const Abreviar_Huella = (Huella) => (Huella ? Huella.slice(0, 8) + '…' + Huella.slice(-4) : '—');
</script>

<template>
    <Head :title="Proyecto.Nombre" />
    <Link href="/proyectos" :class="$style.back">← Proyectos</Link>

    <section class="stack">
        <div class="row">
            <span class="badge badge-navy">{{ Proyecto.Codigo_Unidad }}</span>
            <Componente_Etiqueta_Estado :Estado="Proyecto.Estado" />
            <span v-if="Proyecto.Fecha_Inicio" class="muted" :class="$style.dates"><Componente_Icono Nombre="calendario" :Tamano="15" />{{ Proyecto.Fecha_Inicio }}<template v-if="Proyecto.Fecha_Fin"> – {{ Proyecto.Fecha_Fin }}</template></span>
        </div>
        <h1>{{ Proyecto.Nombre }}</h1>
        <p v-if="Proyecto.Descripcion" :class="$style.lead">{{ Proyecto.Descripcion }}</p>
    </section>

    <ol v-if="Proyecto.Fases.length" :class="$style.phases" class="escalonado" aria-label="Fases del proyecto">
        <li v-for="Fase in Proyecto.Fases" :key="Fase.Posicion" :class="[$style.phase, $style[Fase.Estado]]">
            <span :class="$style.num"><Componente_Icono v-if="Fase.Estado === 'completada'" Nombre="visto" :Tamano="15" :Grosor="2.6" /><template v-else>{{ Fase.Posicion }}</template></span>
            <strong>{{ Fase.Nombre }}</strong>
            <span :class="$style.state">{{ Nombres_Estado_Fase[Fase.Estado] }}<template v-if="Fase.Fecha_Limite"> · {{ Fase.Fecha_Limite }}</template></span>
        </li>
    </ol>

    <Componente_Panel_Aprobacion v-for="Entregable in Entregables_Pendientes" :key="Entregable.Id" :Entregable="Entregable" />

    <section class="stack">
        <div class="row">
            <h2>Entregables</h2>
            <span class="badge badge-blue" title="Cada documento tiene una huella SHA-256 que prueba que no fue alterado"><Componente_Icono Nombre="huella" :Tamano="14" />Con huella digital</span>
        </div>
        <Componente_Estado_Vacio v-if="!Entregables.length" Icono="boveda" Texto="Aún no hay entregables publicados" />
        <div v-else class="table-wrap">
            <table class="table">
                <thead><tr><th>Entregable</th><th>Fase</th><th>Fecha</th><th>Estado</th><th>Huella</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    <tr v-for="Entregable in Entregables" :key="Entregable.Id">
                        <td><strong>{{ Entregable.Titulo }}</strong><div v-if="Entregable.Aprobado_Por" class="muted" :class="$style.small">Aprobado por {{ Entregable.Aprobado_Por }} · {{ Entregable.Fecha_Aprobacion }}</div></td>
                        <td class="mono">{{ Entregable.Etiqueta_Fase ?? '—' }}</td>
                        <td>{{ Entregable.Fecha }}</td>
                        <td><Componente_Etiqueta_Estado :Estado="Entregable.Estado" /></td>
                        <td class="mono" :class="$style.small" :title="Entregable.Huella_Sha256">{{ Abreviar_Huella(Entregable.Huella_Sha256) }}</td>
                        <td><Componente_Boton_Descargar_Entregable :Entregable="Entregable" /></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section v-if="Proyecto.Bitacora.length" class="card stack">
        <div class="row"><span class="burbuja burbuja-sm"><Componente_Icono Nombre="libro" :Tamano="18" /></span><h2>Bitácora</h2></div>
        <ul :class="$style.log" class="escalonado">
            <li v-for="(Entrada, Posicion) in Proyecto.Bitacora" :key="Posicion"><span :class="$style.when">{{ Entrada.Fecha }}</span><span>{{ Entrada.Detalle }}</span></li>
        </ul>
    </section>
</template>

<style module>
.back { align-self: flex-start; font-weight: 700; font-size: 14px; text-decoration: none; padding: 6px 12px; border-radius: 999px; background: var(--surface); border: 1px solid var(--line); }
.dates { display: inline-flex; align-items: center; gap: 6px; }
.lead { font-size: 16px; color: var(--text-2); max-width: 820px; line-height: 1.6; }
.small { font-size: 12px; }
.phases { list-style: none; margin: 0; padding: 0; display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 200px), 1fr)); }
.phase { background: var(--surface); border: 1px solid var(--line); border-top: 4px solid var(--line-2); border-radius: var(--radius); padding: 16px; display: flex; flex-direction: column; gap: 6px; box-shadow: var(--shadow); }
.num { width: 30px; height: 30px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; border: 1.5px solid var(--line-2); color: var(--muted); }
.state { font-size: 13px; color: var(--muted); }
.completada { border-top-color: var(--navy-700); }
.completada .num { background: var(--navy-700); border-color: var(--navy-700); color: #fff; }
.en_curso { border-top-color: var(--navy-700); }
.en_curso .num { border-color: var(--navy-700); color: var(--navy-700); }
.log { list-style: none; margin: 0; padding: 0 0 0 14px; display: flex; flex-direction: column; gap: 16px; border-left: 2px solid var(--blue-100); }
.log li { position: relative; display: flex; flex-direction: column; gap: 2px; font-size: 14px; }
.log li::before { content: ''; position: absolute; left: -21px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: var(--navy-500); box-shadow: 0 0 0 3px #fff; }
.when { font-size: 12px; font-weight: 700; color: var(--muted); }
</style>
