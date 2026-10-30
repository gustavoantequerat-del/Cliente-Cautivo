<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Etiqueta_Estado from '../../Componentes/Componente_Etiqueta_Estado.vue';

const Propiedades = defineProps({ Saldo_Horas: Number, Saldo_Formateado: String, Horas_Por_Vencer: Object, Movimientos: Array, Solicitudes: Array, Especialidades: Array });
const Formulario = useForm({ modalidad: 'consulta', especialidad: Propiedades.Especialidades[0], horas_estimadas: 1, descripcion: '' });
const Opciones_Horas = [0.5, 1, 2, 4];
const Supera_El_Saldo = computed(() => Formulario.horas_estimadas > Propiedades.Saldo_Horas);
const Enviar_Solicitud = () => Formulario.post('/bolsa-de-horas', { preserveScroll: true, onSuccess: () => Formulario.reset('descripcion') });
const Formatear_Horas = (Horas) => String(Horas).replace('.', ',');
</script>

<template>
    <Head title="Bolsa de horas" />
    <Componente_Encabezado_Pagina Titulo="Bolsa de horas de asesoría" Introduccion="Horas incluidas en sus contratos para consultas puntuales o emergencias con especialistas de Blockfinity." />

    <section :class="$style.summary">
        <div :class="$style.balance">
            <span :class="$style.kicker">Horas disponibles</span>
            <strong :class="$style.big">{{ Saldo_Formateado }} h</strong>
            <span v-if="Horas_Por_Vencer" :class="$style.sub">{{ Horas_Por_Vencer.Horas }} h vencen el {{ Horas_Por_Vencer.Fecha }}</span>
        </div>
        <ol :class="$style.how">
            <li><strong>1. Solicite</strong><span>Describa su consulta y estime las horas.</span></li>
            <li><strong>2. Le contactamos</strong><span>Un especialista responde en menos de 4 horas hábiles.</span></li>
            <li><strong>3. Se descuenta</strong><span>Solo se descuentan las horas efectivamente usadas.</span></li>
        </ol>
    </section>

    <div :class="$style.layout">
        <form class="card stack" :class="$style.form" @submit.prevent="Enviar_Solicitud">
            <h2>Canjear horas</h2>
            <div class="chips" role="radiogroup" aria-label="Tipo de solicitud">
                <button type="button" class="chip" :aria-pressed="Formulario.modalidad === 'consulta' ? 'true' : 'false'" @click="Formulario.modalidad = 'consulta'">Consulta programada</button>
                <button type="button" class="chip" :aria-pressed="Formulario.modalidad === 'emergencia' ? 'true' : 'false'" @click="Formulario.modalidad = 'emergencia'">Emergencia</button>
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
                <textarea id="descripcion" v-model="Formulario.descripcion" class="textarea" required minlength="20"
                          placeholder="Contexto, qué necesita resolver y plazos." />
                <span v-if="Formulario.errors.descripcion" class="error">{{ Formulario.errors.descripcion }}</span>
            </div>
            <p class="help">{{ Formulario.modalidad === 'emergencia' ? 'Las emergencias se atienden con prioridad el mismo día hábil.' : 'Coordinaremos una reunión o le responderemos por escrito.' }}</p>
            <button type="submit" class="btn btn-primary" :disabled="Formulario.processing || Supera_El_Saldo || Formulario.descripcion.length < 20">
                {{ Formulario.modalidad === 'emergencia' ? 'Enviar emergencia' : 'Enviar solicitud' }}
            </button>
        </form>

        <div class="stack" :class="$style.side">
            <section class="card stack">
                <h3>Mis solicitudes</h3>
                <p v-if="!Solicitudes.length" class="muted">Aún no hay solicitudes.</p>
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
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Fecha</th><th>Detalle</th><th>Horas</th></tr></thead>
                        <tbody>
                            <tr v-for="(Movimiento, Posicion) in Movimientos" :key="Posicion">
                                <td :class="$style.nowrap">{{ Movimiento.Fecha }}</td>
                                <td>{{ Movimiento.Descripcion }}</td>
                                <td class="mono" :class="Movimiento.Es_Abono ? $style.pos : $style.neg">{{ Movimiento.Horas }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</template>

<style module>
.summary { display: flex; flex-wrap: wrap; gap: 20px; }
.balance { flex: 1 1 280px; background: var(--navy-950); color: #fff; border-radius: var(--radius-lg); padding: 24px; display: flex; flex-direction: column; gap: 6px; }
.kicker { font-size: 11px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: var(--side-label); }
.big { font-size: 44px; font-weight: 800; line-height: 1; }
.sub { color: var(--side-text); font-size: 14px; }
.how { flex: 999 1 420px; list-style: none; margin: 0; padding: 0; display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 180px), 1fr)); }
.how li { background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius); padding: 16px; display: flex; flex-direction: column; gap: 4px; font-size: 14px; }
.how span { color: var(--muted); }
.layout { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; }
.form { flex: 1 1 380px; min-width: 0; }
.side { flex: 1 1 420px; min-width: 0; }
.list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; font-size: 14px; }
.list li { display: flex; flex-direction: column; gap: 4px; border-bottom: 1px solid #E8EBEF; padding-bottom: 10px; }
.resp { background: var(--surface-2); border-radius: 8px; padding: 8px 10px; font-size: 13px; }
.nowrap { white-space: nowrap; }
.pos { color: var(--ok-fg); font-weight: 600; }
.neg { color: var(--text-2); }
</style>
