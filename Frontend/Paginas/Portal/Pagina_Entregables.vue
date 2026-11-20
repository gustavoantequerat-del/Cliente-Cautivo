<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Etiqueta_Estado from '../../Componentes/Componente_Etiqueta_Estado.vue';
import Componente_Paginacion from '../../Componentes/Componente_Paginacion.vue';
import Componente_Calculadora_Codigo from '../../Componentes/Componente_Calculadora_Codigo.vue';
import Componente_Boton_Descargar_Entregable from '../../Componentes/Componente_Boton_Descargar_Entregable.vue';
import Componente_Panel_Aprobacion from '../../Componentes/Componente_Panel_Aprobacion.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';
import Componente_Estado_Vacio from '../../Componentes/Componente_Estado_Vacio.vue';
import Componente_Numero_Animado from '../../Componentes/Componente_Numero_Animado.vue';

const Propiedades = defineProps({ Entregables: Object, Tipos_Entregable: Array, Proyectos: Array, Filtros_Aplicados: Object, Registro_Accesos: Array, Resumen: Object });
const Pagina = usePage();
const Filtros = reactive({ tipo: Propiedades.Filtros_Aplicados.Tipo ?? '', proyecto: Propiedades.Filtros_Aplicados.Proyecto ?? '' });
const Resultado_Verificacion = computed(() => Pagina.props.Mensajes_Flash?.Resultado_Verificacion);
const Entregables_Pendientes = computed(() => Propiedades.Entregables.data.filter((Entregable) => Entregable.Estado === 'pendiente_aprobacion'));
const Esta_Verificando = ref(false);

// Agrupa los entregables de la página por proyecto: se lee más fácil que una tabla larga
const Grupos_Por_Proyecto = computed(() => {
    const Grupos = new Map();
    for (const Entregable of Propiedades.Entregables.data) {
        const Nombre_Grupo = Entregable.Nombre_Proyecto ?? 'Otros documentos';
        if (!Grupos.has(Nombre_Grupo)) Grupos.set(Nombre_Grupo, []);
        Grupos.get(Nombre_Grupo).push(Entregable);
    }
    return [...Grupos].map(([Nombre, Entregables]) => ({ Nombre, Entregables }));
});

function Aplicar_Filtros() {
    const Filtros_Con_Valor = Object.fromEntries(Object.entries(Filtros).filter(([, Valor]) => Valor));
    router.get('/entregables', Filtros_Con_Valor, { preserveState: true, preserveScroll: true });
}
function Alternar_Tipo(Clave_Tipo) {
    Filtros.tipo = Filtros.tipo === Clave_Tipo ? '' : Clave_Tipo;
    Aplicar_Filtros();
}
function Verificar_Codigo(Codigo_Verificacion) {
    Esta_Verificando.value = true;
    router.post('/entregables/verificar', { codigo_verificacion: Codigo_Verificacion }, { preserveScroll: true, onFinish: () => { Esta_Verificando.value = false; } });
}
</script>

<template>
    <Head title="Entregables" />
    <Componente_Encabezado_Pagina Icono="boveda" Titulo="Entregables" Introduccion="Todos los documentos de sus proyectos." />

    <section :class="$style.summary" class="escalonado">
        <div :class="$style.stat"><span class="burbuja burbuja-sm"><Componente_Icono Nombre="archivo" :Tamano="18" /></span><span><strong><Componente_Numero_Animado :Valor="Resumen.Total" /></strong>documentos</span></div>
        <div :class="$style.stat"><span class="burbuja burbuja-sm burbuja-ambar"><Componente_Icono Nombre="alerta" :Tamano="18" /></span><span><strong><Componente_Numero_Animado :Valor="Resumen.Por_Aprobar" /></strong>por aprobar</span></div>
        <div :class="$style.stat"><span class="burbuja burbuja-sm burbuja-verde"><Componente_Icono Nombre="visto" :Tamano="18" /></span><span><strong><Componente_Numero_Animado :Valor="Resumen.Aprobados" /></strong>aprobados</span></div>
    </section>

    <Componente_Panel_Aprobacion v-for="Entregable in Entregables_Pendientes" :key="Entregable.Id" :Entregable="Entregable" />

    <div :class="$style.layout">
        <section class="stack" :class="$style.list">
            <div :class="$style.filters">
                <div class="chips" role="group" aria-label="Filtrar por tipo">
                    <button v-for="Tipo in Tipos_Entregable" :key="Tipo.Clave" type="button" class="chip" :aria-pressed="Filtros.tipo === Tipo.Clave ? 'true' : 'false'" @click="Alternar_Tipo(Tipo.Clave)">
                        {{ Tipo.Nombre }} <span :class="$style.count">{{ Tipo.Cantidad }}</span>
                    </button>
                </div>
                <select v-model="Filtros.proyecto" class="select" :class="$style.select" aria-label="Proyecto" @change="Aplicar_Filtros">
                    <option value="">Todos los proyectos</option>
                    <option v-for="Proyecto in Proyectos" :key="Proyecto.Id" :value="String(Proyecto.Id)">{{ Proyecto.Nombre }}</option>
                </select>
            </div>

            <Componente_Estado_Vacio v-if="!Entregables.data.length" Icono="boveda" Texto="No hay documentos con estos filtros" />
            <div v-for="Grupo in Grupos_Por_Proyecto" :key="Grupo.Nombre" class="stack" :class="$style.group">
                <h3 :class="$style.groupTitle"><Componente_Icono Nombre="proyectos" :Tamano="16" />{{ Grupo.Nombre }}</h3>
                <ul class="lista escalonado">
                    <li v-for="Entregable in Grupo.Entregables" :key="Entregable.Id">
                        <span class="lista-texto">
                            <strong>{{ Entregable.Titulo }}</strong>
                            <span class="lista-detalle">{{ Entregable.Nombre_Tipo }} · {{ Entregable.Fecha }}<template v-if="Entregable.Version"> · v{{ Entregable.Version }}</template></span>
                        </span>
                        <span class="lista-acciones">
                            <Componente_Etiqueta_Estado :Estado="Entregable.Estado" />
                            <Componente_Boton_Descargar_Entregable :Entregable="Entregable" />
                        </span>
                    </li>
                </ul>
            </div>
            <Componente_Paginacion :Enlaces="Entregables.links" />
        </section>

        <aside class="stack" :class="$style.side">
            <div class="card stack">
                <div class="row"><span class="burbuja"><Componente_Icono Nombre="escudo" /></span><div><h3>¿Es auténtica una copia?</h3><span class="muted" :class="$style.small">Compárela con el original registrado.</span></div></div>
                <Componente_Calculadora_Codigo Etiqueta="Suelte aquí el archivo" @Codigo_Calculado="Verificar_Codigo" />
                <p v-if="Esta_Verificando" class="muted row"><Componente_Icono Nombre="cargando" :Tamano="16" class="girando" />Comparando…</p>
                <div v-else-if="Resultado_Verificacion" :class="[$style.result, Resultado_Verificacion.Es_Autentico ? $style.ok : $style.bad]" role="status">
                    <strong class="row"><Componente_Icono :Nombre="Resultado_Verificacion.Es_Autentico ? 'escudo' : 'alerta'" :Tamano="18" />{{ Resultado_Verificacion.Es_Autentico ? 'Es el documento original' : 'No coincide con ningún documento' }}</strong>
                    <span v-if="Resultado_Verificacion.Es_Autentico">«{{ Resultado_Verificacion.Titulo }}» · {{ Resultado_Verificacion.Fecha }}</span>
                    <span v-else>Puede ser otra versión o haber sido modificado.</span>
                </div>
            </div>
            <div v-if="Registro_Accesos.length" class="card stack">
                <div class="row"><span class="burbuja burbuja-sm burbuja-violeta"><Componente_Icono Nombre="usuarios" :Tamano="18" /></span><h3>Actividad reciente</h3></div>
                <ul :class="$style.access">
                    <li v-for="(Acceso, Posicion) in Registro_Accesos" :key="Posicion"><strong>{{ Acceso.Quien }}</strong><span>{{ Acceso.Que }}</span><span class="muted">{{ Acceso.Cuando }}</span></li>
                </ul>
            </div>
        </aside>
    </div>
</template>

<style module>
.summary { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 180px), 1fr)); }
.stat {
  display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius-lg);
  background: var(--surface); border: 1px solid var(--line); box-shadow: var(--shadow);
}
.stat > span:last-child { display: flex; flex-direction: column; font-size: 13px; color: var(--muted); line-height: 1.3; }
.stat strong { font-size: 22px; color: var(--navy-950); }
.layout { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; }
.list { flex: 999 1 560px; min-width: 0; }
.side { flex: 1 1 300px; min-width: 0; position: sticky; top: 90px; }
.filters { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
.count { font-size: 11px; font-weight: 800; padding: 1px 7px; border-radius: 999px; background: rgba(10, 22, 40, 0.08); }
.select { width: auto; min-width: 220px; }
.group { gap: 10px; }
.groupTitle { display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--text-2); }
.small { font-size: 13px; }
.result { display: flex; flex-direction: column; gap: 4px; padding: 14px; border-radius: 12px; font-size: 14px; animation: aparecer-escala 0.35s var(--ease) both; }
.ok { background: var(--ok-bg); color: var(--ok-fg); }
.bad { background: var(--danger-bg); color: var(--danger-fg); }
.access { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; font-size: 13px; }
.access li { display: flex; flex-direction: column; border-bottom: 1px solid #E8EBEF; padding-bottom: 8px; }
.access li:last-child { border-bottom: 0; padding-bottom: 0; }
</style>
