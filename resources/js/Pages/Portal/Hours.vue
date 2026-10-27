<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PageHeader from '../../Components/PageHeader.vue';
import StatusBadge from '../../Components/StatusBadge.vue';

const props = defineProps({ balance: String, balanceRaw: Number, expiring: Object, movements: Array, requests: Array, specialties: Array });
const form = useForm({ mode: 'consulta', specialty: props.specialties[0], estimated_hours: 1, description: '' });
const options = [0.5, 1, 2, 4];
const tooMuch = computed(() => form.estimated_hours > props.balanceRaw);
const submit = () => form.post('/bolsa-de-horas', { preserveScroll: true, onSuccess: () => form.reset('description') });
const fmt = (h) => String(h).replace('.', ',');
</script>

<template>
    <Head title="Bolsa de horas" />
    <PageHeader title="Bolsa de horas de asesoría" intro="Horas incluidas en sus contratos para consultas puntuales o emergencias con especialistas de Blockfinity." />

    <section :class="$style.summary">
        <div :class="$style.balance">
            <span :class="$style.kicker">Horas disponibles</span>
            <strong :class="$style.big">{{ balance }} h</strong>
            <span v-if="expiring" :class="$style.sub">{{ expiring.hours }} h vencen el {{ expiring.date }}</span>
        </div>
        <ol :class="$style.how">
            <li><strong>1. Solicite</strong><span>Describa su consulta y estime las horas.</span></li>
            <li><strong>2. Le contactamos</strong><span>Un especialista responde en menos de 4 horas hábiles.</span></li>
            <li><strong>3. Se descuenta</strong><span>Solo se descuentan las horas efectivamente usadas.</span></li>
        </ol>
    </section>

    <div :class="$style.layout">
        <form class="card stack" :class="$style.form" @submit.prevent="submit">
            <h2>Canjear horas</h2>
            <div class="chips" role="radiogroup" aria-label="Tipo de solicitud">
                <button type="button" class="chip" :aria-pressed="form.mode === 'consulta' ? 'true' : 'false'" @click="form.mode = 'consulta'">Consulta programada</button>
                <button type="button" class="chip" :aria-pressed="form.mode === 'emergencia' ? 'true' : 'false'" @click="form.mode = 'emergencia'">Emergencia</button>
            </div>
            <div class="field">
                <label for="specialty">Especialidad</label>
                <select id="specialty" v-model="form.specialty" class="select">
                    <option v-for="s in specialties" :key="s" :value="s">{{ s }}</option>
                </select>
            </div>
            <div class="field">
                <span class="label">Horas estimadas</span>
                <div class="chips" role="radiogroup" aria-label="Horas estimadas">
                    <button v-for="o in options" :key="o" type="button" class="chip" :aria-pressed="form.estimated_hours === o ? 'true' : 'false'" @click="form.estimated_hours = o">{{ fmt(o) }} h</button>
                </div>
                <span v-if="tooMuch" class="error">Supera su saldo disponible.</span>
                <span v-if="form.errors.estimated_hours" class="error">{{ form.errors.estimated_hours }}</span>
            </div>
            <div class="field">
                <label for="description">Describa su consulta</label>
                <textarea id="description" v-model="form.description" class="textarea" required minlength="20"
                          placeholder="Contexto, qué necesita resolver y plazos." />
                <span v-if="form.errors.description" class="error">{{ form.errors.description }}</span>
            </div>
            <p class="help">{{ form.mode === 'emergencia' ? 'Las emergencias se atienden con prioridad el mismo día hábil.' : 'Coordinaremos una reunión o le responderemos por escrito.' }}</p>
            <button type="submit" class="btn btn-primary" :disabled="form.processing || tooMuch || form.description.length < 20">
                {{ form.mode === 'emergencia' ? 'Enviar emergencia' : 'Enviar solicitud' }}
            </button>
        </form>

        <div class="stack" :class="$style.side">
            <section class="card stack">
                <h3>Mis solicitudes</h3>
                <p v-if="!requests.length" class="muted">Aún no hay solicitudes.</p>
                <ul :class="$style.list">
                    <li v-for="r in requests" :key="r.id">
                        <div class="row between"><strong>{{ r.specialty }}</strong><StatusBadge :status="r.status" /></div>
                        <span class="muted">{{ r.date }} · {{ r.mode }} · {{ r.hours }} h · {{ r.by }}</span>
                        <span v-if="r.response" :class="$style.resp">{{ r.response }}</span>
                    </li>
                </ul>
            </section>
            <section class="card stack">
                <h3>Movimientos</h3>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Fecha</th><th>Detalle</th><th>Horas</th></tr></thead>
                        <tbody>
                            <tr v-for="(m, i) in movements" :key="i">
                                <td :class="$style.nowrap">{{ m.date }}</td>
                                <td>{{ m.description }}</td>
                                <td class="mono" :class="m.positive ? $style.pos : $style.neg">{{ m.hours }}</td>
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
