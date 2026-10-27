<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Codigo_QR from '../../Componentes/Componente_Codigo_QR.vue';

const Propiedades = defineProps({ Tarjeta: Object, Nivel_Alianza: Object, Niveles_Alianza: Array, Videos_Completados: Number, Miembros_Institucion: Array });
const Porcentaje_Nivel = computed(() => {
    const Nivel_Siguiente = Propiedades.Nivel_Alianza.Nivel_Siguiente;
    return Nivel_Siguiente ? Math.min(100, Math.round((Propiedades.Nivel_Alianza.Puntos / Nivel_Siguiente.Puntos_Minimos) * 100)) : 100;
});
// "actual" = nivel de la institución, "alcanzado" = ya superado, "siguiente" = aún no alcanzado
function Obtener_Situacion_Nivel(Nivel) {
    if (Propiedades.Nivel_Alianza.Puntos < Nivel.Puntos_Minimos) return 'siguiente';
    return Nivel.Clave === Propiedades.Nivel_Alianza.Nivel_Actual.Clave ? 'actual' : 'alcanzado';
}
function Obtener_Texto_Situacion(Nivel) {
    const Situacion = Obtener_Situacion_Nivel(Nivel);
    if (Situacion === 'actual') return 'Nivel actual';
    if (Situacion === 'alcanzado') return 'Alcanzado';
    return `Faltan ${Nivel.Puntos_Minimos - Propiedades.Nivel_Alianza.Puntos}`;
}
</script>

<template>
    <Head title="Credencial y beneficios" />
    <Componente_Encabezado_Pagina Titulo="Credencial y beneficios" Introduccion="Su credencial personal de acceso y el nivel de alianza de su institución." />

    <div :class="$style.top">
        <div class="stack" :class="$style.cardCol">
            <div :class="$style.card">
                <div class="row between">
                    <span :class="$style.cardLabel">CREDENCIAL DE ACCESO</span>
                    <span :class="$style.pill">Intransferible</span>
                </div>
                <div class="stack" :class="$style.who">
                    <span :class="$style.name">{{ Tarjeta.Nombre }}</span>
                    <span :class="$style.org">{{ Tarjeta.Area }}<template v-if="Tarjeta.Area"> · </template>{{ Tarjeta.Institucion }}</span>
                </div>
                <div :class="$style.facts">
                    <div><span>NIVEL</span><strong>{{ Nivel_Alianza.Nivel_Actual.Nombre }}</strong></div>
                    <div><span>CREDENCIAL</span><strong class="mono">#{{ Tarjeta.Numero }}</strong></div>
                    <div><span>EMITIDA</span><strong class="mono">{{ Tarjeta.Fecha_Emision }}</strong></div>
                </div>
            </div>
            <p class="muted" :class="$style.small">Su credencial es personal. El administrador de su institución puede revocarla si usted cambia de cargo; en ese caso el QR deja de validar.</p>
        </div>
        <aside v-if="Tarjeta.Url_Verificacion" class="card stack" :class="$style.qrBox">
            <Componente_Codigo_QR :Valor="Tarjeta.Url_Verificacion" :Tamano="150" Texto_Alternativo="Código QR para verificar la credencial" />
            <strong>Verificación pública</strong>
            <p class="muted" :class="$style.small">Muestre este código en eventos o compártalo: cualquiera puede comprobar que su credencial es válida.</p>
            <a :href="Tarjeta.Url_Verificacion" target="_blank" rel="noopener" class="link-arrow">Abrir verificación →</a>
        </aside>
    </div>

    <section class="card stack">
        <div class="row between">
            <div class="stack" :class="$style.tight">
                <h2>Niveles de alianza</h2>
                <p class="muted">El nivel se calcula para toda su institución: cada área suma con formación, eventos y participación.</p>
            </div>
            <span class="badge badge-blue">{{ Nivel_Alianza.Puntos }}<template v-if="Nivel_Alianza.Nivel_Siguiente"> / {{ Nivel_Alianza.Nivel_Siguiente.Puntos_Minimos }}</template> puntos</span>
        </div>
        <div :class="$style.bar"><span :style="{ width: Porcentaje_Nivel + '%' }" /></div>
        <div :class="$style.levels">
            <article v-for="Nivel in Niveles_Alianza" :key="Nivel.Clave" :class="[$style.level, $style[Obtener_Situacion_Nivel(Nivel)]]">
                <span :class="$style.pts">{{ Nivel.Puntos_Minimos }} PTS</span>
                <strong>{{ Nivel.Nombre }}</strong>
                <span :class="$style.tag">{{ Obtener_Texto_Situacion(Nivel) }}</span>
                <p :class="$style.benefit">{{ Nivel.Beneficios }}</p>
            </article>
        </div>
        <p class="muted" :class="$style.small">Los puntos no son transferibles ni canjeables por dinero: reconocen la relación. Usted ha completado {{ Videos_Completados }} contenido(s) de formación.</p>
    </section>

    <section v-if="Miembros_Institucion" class="stack">
        <div class="stack" :class="$style.tight">
            <h2>Credenciales de su organización</h2>
            <p class="muted">Vista de administrador de cuenta. Para emitir o revocar credenciales, escriba a su ejecutiva.</p>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Funcionario</th><th>Área</th><th>Credencial</th><th>Último ingreso</th><th>Estado</th></tr></thead>
                <tbody>
                    <tr v-for="Miembro in Miembros_Institucion" :key="Miembro.Numero">
                        <td><strong>{{ Miembro.Nombre }}</strong></td>
                        <td>{{ Miembro.Area ?? '—' }}</td>
                        <td class="mono">#{{ Miembro.Numero }}</td>
                        <td>{{ Miembro.Ultimo_Ingreso ?? 'Nunca' }}</td>
                        <td><span :class="['badge', Miembro.Esta_Activo ? 'badge-ok' : 'badge-danger']">{{ Miembro.Esta_Activo ? 'Activa' : 'Revocada' }}</span></td>
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
.alcanzado { background: var(--surface-2); border-color: var(--line); }
.actual { background: var(--navy-950); border-color: var(--navy-950); color: #fff; }
.actual strong { color: #fff; }
.pts { font-family: var(--mono); font-size: 12px; color: var(--muted); }
.actual .pts, .actual .tag, .actual .benefit { color: var(--side-text); }
.tag { font-size: 12px; font-weight: 700; color: var(--navy-700); }
.benefit { font-size: 13px; color: var(--text-2); line-height: 1.5; }
@media (max-width: 520px) { .card { aspect-ratio: auto; min-height: 220px; gap: 18px; } }
</style>
