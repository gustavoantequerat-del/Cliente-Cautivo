<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import PageHeader from '../../Components/PageHeader.vue';
import QrCode from '../../Components/QrCode.vue';

const props = defineProps({ card: Object, level: Object, levels: Array, completedVideos: Number, members: Array });
const pct = computed(() => (props.level.next ? Math.min(100, Math.round((props.level.points / props.level.next.points) * 100)) : 100));
const state = (l) => (props.level.points >= l.points ? (l.key === props.level.current.key ? 'now' : 'past') : 'next');
</script>

<template>
    <Head title="Credencial y beneficios" />
    <PageHeader title="Credencial y beneficios" intro="Su credencial personal de acceso y el nivel de alianza de su institución." />

    <div :class="$style.top">
        <div class="stack" :class="$style.cardCol">
            <div :class="$style.card">
                <div class="row between">
                    <span :class="$style.cardLabel">CREDENCIAL DE ACCESO</span>
                    <span :class="$style.pill">Intransferible</span>
                </div>
                <div class="stack" :class="$style.who">
                    <span :class="$style.name">{{ card.name }}</span>
                    <span :class="$style.org">{{ card.area }}<template v-if="card.area"> · </template>{{ card.institution }}</span>
                </div>
                <div :class="$style.facts">
                    <div><span>NIVEL</span><strong>{{ level.current.name }}</strong></div>
                    <div><span>CREDENCIAL</span><strong class="mono">#{{ card.number }}</strong></div>
                    <div><span>EMITIDA</span><strong class="mono">{{ card.issuedAt }}</strong></div>
                </div>
            </div>
            <p class="muted" :class="$style.small">Su credencial es personal. El administrador de su institución puede revocarla si usted cambia de cargo; en ese caso el QR deja de validar.</p>
        </div>
        <aside v-if="card.verifyUrl" class="card stack" :class="$style.qrBox">
            <QrCode :value="card.verifyUrl" :size="150" label="Código QR para verificar la credencial" />
            <strong>Verificación pública</strong>
            <p class="muted" :class="$style.small">Muestre este código en eventos o compártalo: cualquiera puede comprobar que su credencial es válida.</p>
            <a :href="card.verifyUrl" target="_blank" rel="noopener" class="link-arrow">Abrir verificación →</a>
        </aside>
    </div>

    <section class="card stack">
        <div class="row between">
            <div class="stack" :class="$style.tight">
                <h2>Niveles de alianza</h2>
                <p class="muted">El nivel se calcula para toda su institución: cada área suma con formación, eventos y participación.</p>
            </div>
            <span class="badge badge-blue">{{ level.points }}<template v-if="level.next"> / {{ level.next.points }}</template> puntos</span>
        </div>
        <div :class="$style.bar"><span :style="{ width: pct + '%' }" /></div>
        <div :class="$style.levels">
            <article v-for="l in levels" :key="l.key" :class="[$style.level, $style[state(l)]]">
                <span :class="$style.pts">{{ l.points }} PTS</span>
                <strong>{{ l.name }}</strong>
                <span :class="$style.tag">{{ state(l) === 'now' ? 'Nivel actual' : state(l) === 'past' ? 'Alcanzado' : `Faltan ${l.points - level.points}` }}</span>
                <p :class="$style.benefit">{{ l.benefits }}</p>
            </article>
        </div>
        <p class="muted" :class="$style.small">Los puntos no son transferibles ni canjeables por dinero: reconocen la relación. Usted ha completado {{ completedVideos }} contenido(s) de formación.</p>
    </section>

    <section v-if="members" class="stack">
        <div class="stack" :class="$style.tight">
            <h2>Credenciales de su organización</h2>
            <p class="muted">Vista de administrador de cuenta. Para emitir o revocar credenciales, escriba a su ejecutiva.</p>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Funcionario</th><th>Área</th><th>Credencial</th><th>Último ingreso</th><th>Estado</th></tr></thead>
                <tbody>
                    <tr v-for="m in members" :key="m.number">
                        <td><strong>{{ m.name }}</strong></td>
                        <td>{{ m.area ?? '—' }}</td>
                        <td class="mono">#{{ m.number }}</td>
                        <td>{{ m.lastLogin ?? 'Nunca' }}</td>
                        <td><span :class="['badge', m.active ? 'badge-ok' : 'badge-danger']">{{ m.active ? 'Activa' : 'Revocada' }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

<style module>
.top { display: flex; flex-wrap: wrap; gap: 24px; align-items: stretch; }
.cardCol { flex: 999 1 460px; max-width: 620px; }
.card {
  aspect-ratio: 1.586; border-radius: 20px; padding: 26px; color: #fff; display: flex; flex-direction: column; justify-content: space-between;
  background: linear-gradient(135deg, #1F3F73 0%, #0A1628 62%, #1C2633 100%); box-shadow: 0 24px 48px rgba(10, 22, 40, 0.28);
}
.cardLabel { font-size: 11px; font-weight: 700; letter-spacing: 0.16em; color: var(--side-text); }
.pill { font-size: 11px; font-weight: 700; padding: 5px 10px; border-radius: 999px; border: 1px solid #5A6B85; color: #DDE3EB; }
.who { gap: 4px; }
.name { font-size: 26px; font-weight: 800; }
.org { font-size: 14px; color: var(--side-text); }
.facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.facts div { display: flex; flex-direction: column; gap: 2px; }
.facts span { font-size: 10px; letter-spacing: 0.12em; color: #9AA6B8; }
.facts strong { font-size: 13px; }
.qrBox { flex: 1 1 240px; align-items: flex-start; }
.small { font-size: 13px; }
.tight { gap: 4px; }
.bar { height: 8px; border-radius: 99px; background: var(--blue-100); overflow: hidden; }
.bar span { display: block; height: 100%; background: var(--navy-700); }
.levels { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 210px), 1fr)); }
.level { border: 1px solid var(--line-2); border-radius: var(--radius); padding: 16px; display: flex; flex-direction: column; gap: 6px; background: var(--surface); }
.past { background: var(--surface-2); border-color: var(--line); }
.now { background: var(--navy-950); border-color: var(--navy-950); color: #fff; }
.now strong { color: #fff; }
.pts { font-family: var(--mono); font-size: 12px; color: var(--muted); }
.now .pts, .now .tag, .now .benefit { color: var(--side-text); }
.tag { font-size: 12px; font-weight: 700; color: var(--navy-700); }
.benefit { font-size: 13px; color: var(--text-2); line-height: 1.5; }
@media (max-width: 520px) { .card { aspect-ratio: auto; min-height: 220px; gap: 18px; } }
</style>
