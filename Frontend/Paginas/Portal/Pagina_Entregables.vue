<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Etiqueta_Estado from '../../Componentes/Componente_Etiqueta_Estado.vue';
import Componente_Paginacion from '../../Componentes/Componente_Paginacion.vue';
import Componente_Calculadora_Huella from '../../Componentes/Componente_Calculadora_Huella.vue';
import Componente_Boton_Descargar_Entregable from '../../Componentes/Componente_Boton_Descargar_Entregable.vue';
import Componente_Panel_Aprobacion from '../../Componentes/Componente_Panel_Aprobacion.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';
import Componente_Estado_Vacio from '../../Componentes/Componente_Estado_Vacio.vue';

const Propiedades = defineProps({ Entregables: Object, Tipos_Entregable: Array, Proyectos: Array, Filtros_Aplicados: Object, Registro_Accesos: Array });
const Pagina = usePage();
const Filtros = reactive({ tipo: Propiedades.Filtros_Aplicados.Tipo ?? '', proyecto: Propiedades.Filtros_Aplicados.Proyecto ?? '' });
const Resultado_Verificacion = computed(() => Pagina.props.Mensajes_Flash?.Resultado_Verificacion);
const Entregables_Pendientes = computed(() => Propiedades.Entregables.data.filter((Entregable) => Entregable.Estado === 'pendiente_aprobacion'));
const Nombre_Tipo_Filtrado = computed(() => Propiedades.Tipos_Entregable.find((Tipo) => Tipo.Clave === Filtros.tipo)?.Nombre);
const Esta_Verificando = ref(false);

function Aplicar_Filtros() {
    const Filtros_Con_Valor = Object.fromEntries(Object.entries(Filtros).filter(([, Valor]) => Valor));
    router.get('/entregables', Filtros_Con_Valor, { preserveState: true, preserveScroll: true });
}
function Alternar_Tipo(Clave_Tipo) {
    Filtros.tipo = Filtros.tipo === Clave_Tipo ? '' : Clave_Tipo;
    Aplicar_Filtros();
}
function Verificar_Huella(Huella_Sha256) {
    Esta_Verificando.value = true;
    router.post('/entregables/verificar', { huella_sha256: Huella_Sha256 }, { preserveScroll: true, onFinish: () => { Esta_Verificando.value = false; } });
}
const Abreviar_Huella = (Huella) => (Huella ? Huella.slice(0, 8) + '…' : '—');
</script>

<template>
    <Head title="Historial de entregables" />
    <Componente_Encabezado_Pagina Icono="boveda" Antetitulo="Bóveda" Titulo="Entregables" Introduccion="Todos los documentos de sus proyectos." />

    <Componente_Panel_Aprobacion v-for="Entregable in Entregables_Pendientes" :key="Entregable.Id" :Entregable="Entregable" />

    <div class="chips" role="group" aria-label="Filtrar por tipo">
        <button v-for="Tipo in Tipos_Entregable" :key="Tipo.Clave" type="button" class="chip" :aria-pressed="Filtros.tipo === Tipo.Clave ? 'true' : 'false'" @click="Alternar_Tipo(Tipo.Clave)">
            {{ Tipo.Nombre }} · {{ Tipo.Cantidad }}
        </button>
    </div>

    <div :class="$style.layout">
        <section class="stack" :class="$style.list">
            <div class="row between">
                <h2>{{ Nombre_Tipo_Filtrado ?? 'Todos los documentos' }}</h2>
                <label class="row">
                    <Componente_Icono Nombre="proyectos" :Tamano="18" />
                    <span class="sr-only">Proyecto</span>
                    <select v-model="Filtros.proyecto" class="select" :class="$style.select" @change="Aplicar_Filtros">
                        <option value="">Todos los proyectos</option>
                        <option v-for="Proyecto in Proyectos" :key="Proyecto.Id" :value="Proyecto.Id">{{ Proyecto.Nombre }}</option>
                    </select>
                </label>
            </div>
            <Componente_Estado_Vacio v-if="!Entregables.data.length" Icono="boveda" Texto="No hay documentos con estos filtros" />
            <div v-else class="table-wrap">
                <table class="table">
                    <thead><tr><th>Documento</th><th>Unidad</th><th>Fecha</th><th>Estado</th><th>Huella</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                    <tbody>
                        <tr v-for="Entregable in Entregables.data" :key="Entregable.Id">
                            <td>
                                <strong>{{ Entregable.Titulo }}</strong>
                                <div class="muted" :class="$style.small">{{ Entregable.Nombre_Tipo }}<template v-if="Entregable.Version"> · v{{ Entregable.Version }}</template><template v-if="Entregable.Nombre_Proyecto"> · {{ Entregable.Nombre_Proyecto }}</template></div>
                            </td>
                            <td><span class="badge badge-outline">{{ Entregable.Codigo_Unidad ?? '—' }}</span></td>
                            <td>{{ Entregable.Fecha }}</td>
                            <td><Componente_Etiqueta_Estado :Estado="Entregable.Estado" /></td>
                            <td class="mono" :class="$style.small" :title="Entregable.Huella_Sha256">{{ Abreviar_Huella(Entregable.Huella_Sha256) }}</td>
                            <td><Componente_Boton_Descargar_Entregable :Entregable="Entregable" /></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Componente_Paginacion :Enlaces="Entregables.links" />
        </section>

        <aside class="stack" :class="$style.side">
            <div class="card stack" :class="$style.verify">
                <div class="row"><span class="burbuja"><Componente_Icono Nombre="huella" /></span><div><h3>Verificar documento</h3><span class="muted" :class="$style.small">¿Es auténtica su copia?</span></div></div>
                <Componente_Calculadora_Huella @Huella_Calculada="Verificar_Huella" />
                <p v-if="Esta_Verificando" class="muted row"><Componente_Icono Nombre="cargando" :Tamano="16" class="girando" />Comparando…</p>
                <div v-else-if="Resultado_Verificacion" :class="[$style.result, Resultado_Verificacion.Es_Autentico ? $style.ok : $style.bad]" role="status">
                    <strong class="row"><Componente_Icono :Nombre="Resultado_Verificacion.Es_Autentico ? 'escudo' : 'alerta'" :Tamano="18" />{{ Resultado_Verificacion.Es_Autentico ? 'Documento auténtico' : 'Sin coincidencias' }}</strong>
                    <span v-if="Resultado_Verificacion.Es_Autentico">Coincide con «{{ Resultado_Verificacion.Titulo }}» ({{ Resultado_Verificacion.Fecha }}).</span>
                    <span v-else>Puede haber sido modificado.</span>
                    <span class="mono" :class="$style.hash">{{ Resultado_Verificacion.Huella }}</span>
                </div>
            </div>
            <div v-if="Registro_Accesos.length" class="card stack">
                <div class="row"><span class="burbuja burbuja-sm burbuja-violeta"><Componente_Icono Nombre="usuarios" :Tamano="18" /></span><h3>Accesos recientes</h3></div>
                <ul :class="$style.access">
                    <li v-for="(Acceso, Posicion) in Registro_Accesos" :key="Posicion"><strong>{{ Acceso.Quien }}</strong><span>{{ Acceso.Que }}</span><span class="muted">{{ Acceso.Cuando }}</span></li>
                </ul>
            </div>
        </aside>
    </div>
</template>

<style module>
.layout { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; }
.list { flex: 999 1 560px; min-width: 0; }
.side { flex: 1 1 300px; min-width: 0; }
.select { width: auto; min-width: 200px; }
.small { font-size: 12px; }
.verify { position: sticky; top: 90px; }
.result { display: flex; flex-direction: column; gap: 4px; padding: 14px; border-radius: 12px; font-size: 14px; animation: aparecer-escala 0.35s var(--ease) both; }
.ok { background: var(--ok-bg); color: var(--ok-fg); }
.bad { background: var(--danger-bg); color: var(--danger-fg); }
.hash { font-size: 11px; word-break: break-all; opacity: 0.8; }
.access { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; font-size: 13px; }
.access li { display: flex; flex-direction: column; border-bottom: 1px solid #E8EBEF; padding-bottom: 8px; }
</style>
