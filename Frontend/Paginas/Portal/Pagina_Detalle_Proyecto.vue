<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Componente_Etiqueta_Estado from '../../Componentes/Componente_Etiqueta_Estado.vue';
import Componente_Panel_Aprobacion from '../../Componentes/Componente_Panel_Aprobacion.vue';
import Componente_Boton_Descargar_Entregable from '../../Componentes/Componente_Boton_Descargar_Entregable.vue';
import Componente_Estado_Vacio from '../../Componentes/Componente_Estado_Vacio.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

const Propiedades = defineProps({ Proyecto: Object, Entregables: Array, Equipo: Array });
const Es_Administrador_Delegado = computed(() => usePage().props.Usuario_Actual.Es_Administrador_Delegado);
const Entregables_Pendientes = computed(() => (Es_Administrador_Delegado.value
    ? Propiedades.Entregables.filter((Entregable) => Entregable.Estado === 'pendiente_aprobacion') : []));
const Nombres_Estado_Fase = { completada: 'Completada', en_curso: 'En curso', pendiente: 'Pendiente' };
const Cantidad_Inscritos = computed(() => (Propiedades.Equipo ?? []).filter((Miembro) => Miembro.Esta_Inscrito).length);
const Miembro_Guardando = ref(null);

function Cambiar_Inscripcion(Miembro) {
    Miembro_Guardando.value = Miembro.Id;
    router.post(`/proyectos/${Propiedades.Proyecto.Id}/participantes`, { usuario_id: Miembro.Id, inscrito: !Miembro.Esta_Inscrito },
        { preserveScroll: true, onFinish: () => { Miembro_Guardando.value = null; } });
}
</script>

<template>
    <Head :title="Proyecto.Nombre" />
    <Link href="/proyectos" :class="$style.back">← Proyectos</Link>

    <section :class="$style.head" class="card">
        <div class="row">
            <span class="badge badge-navy">{{ Proyecto.Codigo_Unidad ?? 'Blockfinity' }}</span>
            <Componente_Etiqueta_Estado :Estado="Proyecto.Estado" />
            <span v-if="Proyecto.Fecha_Inicio" class="muted" :class="$style.inline"><Componente_Icono Nombre="calendario" :Tamano="15" />{{ Proyecto.Fecha_Inicio }}<template v-if="Proyecto.Fecha_Fin"> – {{ Proyecto.Fecha_Fin }}</template></span>
        </div>
        <h1>{{ Proyecto.Nombre }}</h1>
        <p v-if="Proyecto.Descripcion" :class="$style.lead">{{ Proyecto.Descripcion }}</p>
        <div :class="$style.progress">
            <div class="barra" :class="$style.bar"><span :style="{ width: Proyecto.Avance + '%' }" /></div>
            <strong>{{ Proyecto.Avance }}%</strong>
        </div>
        <span v-if="Proyecto.Proximo_Hito" :class="$style.milestone"><Componente_Icono Nombre="estrella" :Tamano="15" />Próximo hito: {{ Proyecto.Proximo_Hito }}</span>
    </section>

    <ol v-if="Proyecto.Fases.length" :class="$style.phases" class="escalonado" aria-label="Fases del proyecto">
        <li v-for="Fase in Proyecto.Fases" :key="Fase.Posicion" :class="[$style.phase, $style[Fase.Estado]]">
            <span :class="$style.num"><Componente_Icono v-if="Fase.Estado === 'completada'" Nombre="visto" :Tamano="15" :Grosor="2.6" /><template v-else>{{ Fase.Posicion }}</template></span>
            <span :class="$style.phaseText">
                <strong>{{ Fase.Nombre }}</strong>
                <span :class="$style.state">{{ Nombres_Estado_Fase[Fase.Estado] }}<template v-if="Fase.Fecha_Limite"> · {{ Fase.Fecha_Limite }}</template></span>
            </span>
        </li>
    </ol>

    <Componente_Panel_Aprobacion v-for="Entregable in Entregables_Pendientes" :key="Entregable.Id" :Entregable="Entregable" />

    <div :class="$style.columns">
        <section class="stack" :class="$style.main">
            <h2>Entregables</h2>
            <Componente_Estado_Vacio v-if="!Entregables.length" Icono="boveda" Texto="Aún no hay entregables publicados" />
            <ul v-else class="lista escalonado">
                <li v-for="Entregable in Entregables" :key="Entregable.Id">
                    <span class="burbuja burbuja-sm"><Componente_Icono Nombre="archivo" :Tamano="18" /></span>
                    <span class="lista-texto">
                        <strong>{{ Entregable.Titulo }}</strong>
                        <span class="lista-detalle">
                            {{ Entregable.Fecha }}<template v-if="Entregable.Etiqueta_Fase"> · Fase {{ Entregable.Etiqueta_Fase }}</template><template v-if="Entregable.Aprobado_Por"> · Aprobado por {{ Entregable.Aprobado_Por }}</template>
                        </span>
                    </span>
                    <span class="lista-acciones">
                        <Componente_Etiqueta_Estado :Estado="Entregable.Estado" />
                        <Componente_Boton_Descargar_Entregable :Entregable="Entregable" />
                    </span>
                </li>
            </ul>

            <template v-if="Proyecto.Bitacora.length">
                <h2>Novedades</h2>
                <ul :class="$style.log" class="card escalonado">
                    <li v-for="(Entrada, Posicion) in Proyecto.Bitacora" :key="Posicion"><span :class="$style.when">{{ Entrada.Fecha }}</span><span>{{ Entrada.Detalle }}</span></li>
                </ul>
            </template>
        </section>

        <aside v-if="Equipo" class="card stack" :class="$style.team">
            <div class="row between">
                <div class="row"><span class="burbuja burbuja-sm burbuja-violeta"><Componente_Icono Nombre="usuarios" :Tamano="18" /></span><h3>Equipo del proyecto</h3></div>
                <span class="badge badge-blue">{{ Cantidad_Inscritos }}/{{ Equipo.length }}</span>
            </div>
            <p class="muted" :class="$style.hint">Quien esté activado verá este proyecto en su portal.</p>
            <p v-if="!Equipo.length" class="vacio">Aún no hay usuarios institucionales en su institución. Pídalos a su ejecutiva.</p>
            <ul :class="$style.members">
                <li v-for="Miembro in Equipo" :key="Miembro.Id">
                    <span :class="$style.avatar">{{ Miembro.Iniciales }}</span>
                    <span class="lista-texto">
                        <strong>{{ Miembro.Nombre }}</strong>
                        <span v-if="Miembro.Area" class="lista-detalle">{{ Miembro.Area }}</span>
                    </span>
                    <button type="button" role="switch" :aria-checked="Miembro.Esta_Inscrito ? 'true' : 'false'" :aria-label="`${Miembro.Esta_Inscrito ? 'Quitar a' : 'Inscribir a'} ${Miembro.Nombre}`"
                            :class="[$style.switch, Miembro.Esta_Inscrito && $style.on]" :disabled="Miembro_Guardando === Miembro.Id" @click="Cambiar_Inscripcion(Miembro)">
                        <span :class="$style.knob" />
                    </button>
                </li>
            </ul>
        </aside>
    </div>
</template>

<style module>
.back { align-self: flex-start; font-weight: 700; font-size: 14px; text-decoration: none; padding: 6px 12px; border-radius: 999px; background: var(--surface); border: 1px solid var(--line); }
.head { display: flex; flex-direction: column; gap: 14px; }
.inline { display: inline-flex; align-items: center; gap: 6px; }
.lead { font-size: 15px; color: var(--text-2); max-width: 820px; line-height: 1.6; }
.progress { display: flex; align-items: center; gap: 12px; }
.progress strong { font-size: 16px; color: var(--navy-700); }
.bar { flex: 1; max-width: 520px; }
.milestone { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: var(--navy-700); }
.phases { list-style: none; margin: 0; padding: 0; display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 200px), 1fr)); }
.phase {
  display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius-lg);
  background: var(--surface); border: 1px solid var(--line); box-shadow: var(--shadow);
}
.phaseText { display: flex; flex-direction: column; min-width: 0; }
.num {
  width: 32px; height: 32px; flex: none; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 800; border: 1.5px solid var(--line-2); color: var(--muted);
}
.state { font-size: 12px; color: var(--muted); }
.completada .num { background: var(--navy-700); border-color: var(--navy-700); color: #fff; }
.en_curso { border-color: var(--navy-500); box-shadow: 0 0 0 3px rgba(59, 91, 140, 0.12); }
.en_curso .num { border-color: var(--navy-700); color: var(--navy-700); }
.columns { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; }
.main { flex: 999 1 520px; min-width: 0; }
.team { flex: 1 1 300px; min-width: 0; position: sticky; top: 90px; }
.hint { font-size: 13px; }
.members { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; }
.members li { display: flex; align-items: center; gap: 12px; }
.avatar {
  width: 36px; height: 36px; flex: none; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
  background: var(--blue-100); color: var(--navy-700); font-size: 13px; font-weight: 800;
}
.switch {
  position: relative; width: 44px; height: 26px; flex: none; border-radius: 999px; border: 0; cursor: pointer;
  background: var(--line-2); transition: background 0.2s;
}
.switch:disabled { opacity: 0.6; cursor: wait; }
.on { background: #3E9B66; }
.knob {
  position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 50%; background: #fff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25); transition: transform 0.2s var(--ease);
}
.on .knob { transform: translateX(18px); }
.log { list-style: none; margin: 0; padding: 20px 24px 20px 38px; display: flex; flex-direction: column; gap: 16px; }
.log li { position: relative; display: flex; flex-direction: column; gap: 2px; font-size: 14px; }
.log li::before { content: ''; position: absolute; left: -20px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: var(--navy-500); box-shadow: 0 0 0 3px var(--blue-100); }
.when { font-size: 12px; font-weight: 700; color: var(--muted); }
</style>
