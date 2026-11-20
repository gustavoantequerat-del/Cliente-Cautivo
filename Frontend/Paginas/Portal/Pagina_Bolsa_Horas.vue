<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Etiqueta_Estado from '../../Componentes/Componente_Etiqueta_Estado.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';
import Componente_Numero_Animado from '../../Componentes/Componente_Numero_Animado.vue';

const Propiedades = defineProps({ Saldo_Horas: Number, Saldo_Formateado: String, Horas_Por_Vencer: Object, Movimientos: Array, Solicitudes: Array, Especialidades: Array });
const Formulario = useForm({ modalidad: 'consulta', especialidad: Propiedades.Especialidades[0], horas_estimadas: 1, descripcion: '' });
const Opciones_Horas = [0.5, 1, 2, 4];
const Supera_El_Saldo = computed(() => Formulario.horas_estimadas > Propiedades.Saldo_Horas);
const Enviar_Solicitud = () => Formulario.post('/bolsa-de-horas', { preserveScroll: true, onSuccess: () => Formulario.reset('descripcion') });
const Formatear_Horas = (Horas) => String(Horas).replace('.', ',');
const Minimo_Caracteres = 20;
const Maximo_Caracteres = 3000;
const Faltan_Caracteres = computed(() => Math.max(0, Minimo_Caracteres - Formulario.descripcion.trim().length));
const Pasos = [
    { Icono: 'lapiz', Titulo: 'Solicite' },
    { Icono: 'usuario', Titulo: 'Le contactamos en < 4 h' },
    { Icono: 'reloj', Titulo: 'Solo se descuenta lo usado' },
];
</script>

<template>
    <Head title="Bolsa de horas" />
    <Componente_Encabezado_Pagina Icono="reloj" Titulo="Bolsa de horas" Introduccion="Consultas y emergencias con especialistas." />

    <section :class="$style.summary">
        <div :class="$style.balance">
            <span :class="$style.kicker">Horas disponibles</span>
            <strong :class="$style.big" :aria-label="`${Saldo_Formateado} horas`"><Componente_Numero_Animado :Valor="Saldo_Horas" :Decimales="2" /> h</strong>
            <span v-if="Horas_Por_Vencer" :class="$style.sub"><Componente_Icono Nombre="alerta" :Tamano="14" />{{ Horas_Por_Vencer.Horas }} h vencen el {{ Horas_Por_Vencer.Fecha }}</span>
        </div>
        <ol :class="$style.how" class="escalonado">
            <li v-for="(Paso, Posicion) in Pasos" :key="Paso.Titulo">
                <span class="burbuja burbuja-sm"><Componente_Icono :Nombre="Paso.Icono" :Tamano="18" /></span>
                <span :class="$style.step">{{ Posicion + 1 }}</span><strong>{{ Paso.Titulo }}</strong>
            </li>
        </ol>
    </section>

    <div :class="$style.layout">
        <form class="card stack" :class="$style.form" @submit.prevent="Enviar_Solicitud">
            <h2>Canjear horas</h2>
            <div class="chips" role="radiogroup" aria-label="Tipo de solicitud">
                <button type="button" class="chip" :aria-pressed="Formulario.modalidad === 'consulta' ? 'true' : 'false'" @click="Formulario.modalidad = 'consulta'"><Componente_Icono Nombre="calendario" :Tamano="15" />Consulta</button>
                <button type="button" class="chip" :aria-pressed="Formulario.modalidad === 'emergencia' ? 'true' : 'false'" @click="Formulario.modalidad = 'emergencia'"><Componente_Icono Nombre="rayo" :Tamano="15" />Emergencia</button>
            </div>
            <div class="field">
                <label for="especialidad">Especialidad</label>
                <select id="especialidad" v-model="Formulario.especialidad" class="select">
                    <option v-for="Especialidad in Especialidades" :key="Especialidad" :value="Especialidad">{{ Especialidad }}</option>
                </select>
            </div>
            <div class="field">
                <span class="label">Horas estimadas</span>
                <div class="chips" role="radiogroup" aria-label="Horas estimadas">
                    <button v-for="Horas in Opciones_Horas" :key="Horas" type="button" class="chip" :aria-pressed="Formulario.horas_estimadas === Horas ? 'true' : 'false'" @click="Formulario.horas_estimadas = Horas">{{ Formatear_Horas(Horas) }} h</button>
                </div>
                <span v-if="Supera_El_Saldo" class="error">Supera su saldo disponible.</span>
                <span v-if="Formulario.errors.horas_estimadas" class="error">{{ Formulario.errors.horas_estimadas }}</span>
            </div>
            <div class="field">
                <label for="descripcion">Describa su consulta</label>
                <textarea id="descripcion" v-model="Formulario.descripcion" class="textarea" required :minlength="Minimo_Caracteres" :maxlength="Maximo_Caracteres"
                          placeholder="¿Qué necesita resolver y para cuándo?" />
                <span :class="[$style.counter, !Faltan_Caracteres && $style.counterOk]">
                    {{ Faltan_Caracteres ? `Faltan ${Faltan_Caracteres} caracteres` : '✓ Listo' }} · {{ Formulario.descripcion.length }}/{{ Maximo_Caracteres }}
                </span>
                <span v-if="Formulario.errors.descripcion" class="error">{{ Formulario.errors.descripcion }}</span>
            </div>
            <button type="submit" class="btn btn-primary" :disabled="Formulario.processing || Supera_El_Saldo || Faltan_Caracteres > 0">
                <Componente_Icono :Nombre="Formulario.modalidad === 'emergencia' ? 'rayo' : 'correo'" :Tamano="16" />
                {{ Formulario.modalidad === 'emergencia' ? 'Enviar emergencia' : 'Enviar solicitud' }}
            </button>
        </form>

        <div class="stack" :class="$style.side">
            <section class="card stack">
                <h3>Mis solicitudes</h3>
                <p v-if="!Solicitudes.length" class="vacio">Aún no hay solicitudes.</p>
                <ul :class="$style.list">
                    <li v-for="Solicitud in Solicitudes" :key="Solicitud.Id">
                        <div class="row between"><strong>{{ Solicitud.Especialidad }}</strong><Componente_Etiqueta_Estado :Estado="Solicitud.Estado" /></div>
                        <span class="muted">{{ Solicitud.Fecha }} · {{ Solicitud.Modalidad }} · {{ Solicitud.Horas }} h · {{ Solicitud.Solicitante }}</span>
                        <span v-if="Solicitud.Respuesta" :class="$style.resp">{{ Solicitud.Respuesta }}</span>
                    </li>
                </ul>
            </section>
            <section class="card stack">
                <h3>Movimientos</h3>
                <ul :class="$style.moves">
                    <li v-for="(Movimiento, Posicion) in Movimientos" :key="Posicion">
                        <span :class="[$style.moveIcon, Movimiento.Es_Abono ? $style.pos : $style.neg]"><Componente_Icono :Nombre="Movimiento.Es_Abono ? 'agregar' : 'reloj'" :Tamano="14" /></span>
                        <span class="lista-texto"><span>{{ Movimiento.Descripcion }}</span><span class="lista-detalle">{{ Movimiento.Fecha }}</span></span>
                        <strong :class="Movimiento.Es_Abono ? $style.pos : $style.neg">{{ Movimiento.Horas }} h</strong>
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>

<style module>
.summary { display: flex; flex-wrap: wrap; gap: 20px; }
.balance { flex: 1 1 280px; background: var(--gradient-hero); box-shadow: 0 18px 40px rgba(15, 34, 64, 0.22); color: #fff; border-radius: var(--radius-lg); padding: 24px; display: flex; flex-direction: column; gap: 6px; }
.kicker { font-size: 11px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: var(--side-label); }
.big { font-size: 44px; font-weight: 800; line-height: 1; }
.sub { display: inline-flex; align-items: center; gap: 6px; color: #FFD9A0; font-size: 14px; }
.how { flex: 999 1 420px; list-style: none; margin: 0; padding: 0; display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 180px), 1fr)); }
.how li { position: relative; background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 16px; display: flex; flex-direction: column; gap: 10px; font-size: 14px; box-shadow: var(--shadow); }
.step { position: absolute; top: 14px; right: 16px; font-size: 28px; font-weight: 800; color: var(--blue-100); line-height: 1; }
.counter { font-size: 12px; color: var(--muted); align-self: flex-end; }
.counterOk { color: var(--ok-fg); font-weight: 700; }
.layout { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; }
.form { flex: 1 1 380px; min-width: 0; }
.side { flex: 1 1 420px; min-width: 0; }
.list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; font-size: 14px; }
.list li { display: flex; flex-direction: column; gap: 4px; border-bottom: 1px solid #E8EBEF; padding-bottom: 10px; }
.resp { background: var(--surface-2); border-radius: 8px; padding: 8px 10px; font-size: 13px; }
.moves { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; font-size: 14px; }
.moves li { display: flex; align-items: center; gap: 12px; }
.moveIcon { width: 28px; height: 28px; flex: none; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: var(--surface-2); }
.pos { color: var(--ok-fg); }
.neg { color: var(--text-2); }
</style>
