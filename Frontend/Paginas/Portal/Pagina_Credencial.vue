<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Codigo_QR from '../../Componentes/Componente_Codigo_QR.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

const Propiedades = defineProps({ Tarjeta: Object, Nivel_Alianza: Object, Videos_Completados: Number, Miembros_Institucion: Array });
const Contratados = computed(() => Math.min(Propiedades.Nivel_Alianza.Servicios_Contratados, Propiedades.Nivel_Alianza.Servicios_Para_Premium));
const Porcentaje_Nivel = computed(() => Math.round((Contratados.value / Propiedades.Nivel_Alianza.Servicios_Para_Premium) * 100));
const Esta_Alcanzado = (Nivel) => Propiedades.Nivel_Alianza.Servicios_Contratados >= Nivel.Servicios_Minimos;
const Texto_Requisito = (Nivel) => (Nivel === Propiedades.Nivel_Alianza.Niveles.at(-1)
    ? `${Nivel.Servicios_Minimos} O MÁS PRODUCTOS` : `${Nivel.Servicios_Minimos} PRODUCTO${Nivel.Servicios_Minimos === 1 ? '' : 'S'}`);
// Ej. "1 producto más para Premium"
const Texto_Faltante = computed(() => {
    const { Nivel_Siguiente, Servicios_Faltantes } = Propiedades.Nivel_Alianza;
    if (!Nivel_Siguiente) return '¡Su institución tiene todos los beneficios!';
    return `${Servicios_Faltantes} producto${Servicios_Faltantes === 1 ? '' : 's'} más para ${Nivel_Siguiente.Nombre}`;
});
</script>

<template>
    <Head title="Credencial y beneficios" />
    <Componente_Encabezado_Pagina Icono="credencial" Titulo="Credencial y beneficios" Introduccion="Su acceso personal y el nivel de su institución." />

    <div :class="$style.top">
        <div class="stack" :class="$style.cardCol">
            <div :class="[$style.card, $style['card_' + Nivel_Alianza.Nivel_Actual.Clave]]">
                <div class="row between">
                    <span :class="$style.cardLabel">CREDENCIAL DE ACCESO</span>
                    <span :class="$style.pill">Intransferible</span>
                </div>
                <div :class="$style.identity">
                    <span :class="$style.cardInitials">{{ $page.props.Usuario_Actual.Iniciales }}</span>
                    <div class="stack" :class="$style.who">
                        <span :class="$style.name">{{ Tarjeta.Nombre }}</span>
                        <span :class="$style.org">{{ Tarjeta.Area }}<template v-if="Tarjeta.Area"> · </template>{{ Tarjeta.Institucion }}</span>
                    </div>
                </div>
                <div :class="$style.facts">
                    <div><span>NIVEL</span><strong>{{ Nivel_Alianza.Nivel_Actual.Nombre }}</strong></div>
                    <div><span>CREDENCIAL</span><strong class="mono">#{{ Tarjeta.Numero }}</strong></div>
                    <div><span>EMITIDA</span><strong class="mono">{{ Tarjeta.Fecha_Emision }}</strong></div>
                </div>
            </div>
            <p class="muted" :class="$style.small"><Componente_Icono Nombre="candado" :Tamano="14" /> Personal e intransferible.</p>
        </div>
        <aside v-if="Tarjeta.Url_Verificacion" class="card stack" :class="$style.qrBox">
            <Componente_Codigo_QR :Valor="Tarjeta.Url_Verificacion" :Tamano="150" Texto_Alternativo="Código QR para verificar la credencial" />
            <strong>Escanee para verificar</strong>
            <a :href="Tarjeta.Url_Verificacion" target="_blank" rel="noopener" class="link-arrow">Abrir <Componente_Icono Nombre="externo" :Tamano="15" /></a>
        </aside>
    </div>

    <section class="card stack">
        <div class="row between">
            <div class="row"><span class="burbuja burbuja-ambar"><Componente_Icono Nombre="estrella" /></span><h2>Niveles de alianza</h2></div>
            <span class="badge badge-blue">{{ Contratados }} de {{ Nivel_Alianza.Servicios_Para_Premium }} productos activos para Premium</span>
        </div>
        <div class="barra"><span :style="{ width: Porcentaje_Nivel + '%' }" /></div>
        <strong :class="$style.next">{{ Texto_Faltante }}</strong>
        <div :class="$style.levels" class="escalonado">
            <article v-for="Nivel in Nivel_Alianza.Niveles" :key="Nivel.Clave"
                :class="[$style.level, Nivel.Clave === Nivel_Alianza.Nivel_Actual.Clave ? $style.actual : !Esta_Alcanzado(Nivel) && $style.bloqueado]">
                <span :class="$style.pts"><span :class="$style.swatch" :style="{ background: Nivel.Color }" />{{ Texto_Requisito(Nivel) }}</span>
                <strong>{{ Nivel.Nombre }}</strong>
                <span :class="$style.tag">
                    <template v-if="Nivel.Clave === Nivel_Alianza.Nivel_Actual.Clave">Su nivel actual</template>
                    <template v-else-if="Esta_Alcanzado(Nivel)">Incluido</template>
                    <template v-else><Componente_Icono Nombre="candado" :Tamano="12" /> Se desbloquea en {{ Nivel.Nombre }}</template>
                </span>
                <ul :class="$style.benefits">
                    <li v-for="Beneficio in Nivel.Beneficios" :key="Beneficio">
                        <Componente_Icono :Nombre="Esta_Alcanzado(Nivel) ? 'visto' : 'candado'" :Tamano="14" />{{ Beneficio }}
                    </li>
                </ul>
            </article>
        </div>
        <p class="muted" :class="$style.small"><Componente_Icono Nombre="info" :Tamano="14" /> El nivel depende de cuántos productos o servicios de Blockfinity tiene activos su institución.</p>
    </section>

    <section v-if="Miembros_Institucion" class="stack">
        <div class="row between">
            <h2>Su equipo</h2>
            <span class="badge badge-blue">{{ Miembros_Institucion.filter((Miembro) => Miembro.Esta_Activo).length }} con acceso</span>
        </div>
        <div :class="$style.members" class="escalonado">
            <article v-for="Miembro in Miembros_Institucion" :key="Miembro.Numero" :class="[$style.member, !Miembro.Esta_Activo && $style.memberOff]">
                <span :class="$style.memberAvatar">{{ Miembro.Iniciales }}</span>
                <span class="lista-texto">
                    <strong>{{ Miembro.Nombre }}</strong>
                    <span class="lista-detalle">{{ Miembro.Nombre_Rol }}<template v-if="Miembro.Area"> · {{ Miembro.Area }}</template></span>
                    <span class="lista-detalle">{{ Miembro.Ultimo_Ingreso ? `Último ingreso: ${Miembro.Ultimo_Ingreso}` : 'Aún no ingresó' }}</span>
                </span>
                <span :class="['badge', Miembro.Esta_Activo ? 'badge-ok' : 'badge-danger']">{{ Miembro.Esta_Activo ? 'Activa' : 'Revocada' }}</span>
            </article>
        </div>
        <p class="muted" :class="$style.small"><Componente_Icono Nombre="info" :Tamano="14" /> Para dar de alta o quitar personas, escriba a su ejecutiva de cuenta.</p>
    </section>
</template>

<style module>
.top { display: flex; flex-wrap: wrap; gap: 24px; align-items: stretch; }
.cardCol { flex: 999 1 460px; max-width: 620px; }
.card {
  position: relative; overflow: hidden; transition: transform 0.4s var(--ease), box-shadow 0.4s;
  aspect-ratio: 1.586; border-radius: 20px; padding: 26px; color: #fff; display: flex; flex-direction: column; justify-content: space-between;
  background: linear-gradient(135deg, #1F3F73 0%, #0A1628 62%, #1C2633 100%); box-shadow: 0 24px 48px rgba(10, 22, 40, 0.28);
}
.card::after {
  content: ''; position: absolute; inset: 0; pointer-events: none;
  background: linear-gradient(115deg, transparent 30%, rgba(255, 255, 255, 0.16) 45%, transparent 60%);
  transform: translateX(-100%); animation: brillo 4s var(--ease) 0.6s infinite;
}
.card:hover { transform: perspective(900px) rotateX(3deg) rotateY(-4deg) translateY(-4px); box-shadow: 0 34px 60px rgba(10, 22, 40, 0.34); }
@keyframes brillo { 0%, 60% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
.cardLabel { font-size: 11px; font-weight: 700; letter-spacing: 0.16em; color: var(--side-text); }
.pill { font-size: 11px; font-weight: 700; padding: 5px 10px; border-radius: 999px; border: 1px solid #5A6B85; color: #DDE3EB; }
.identity { display: flex; align-items: center; gap: 14px; }
.cardInitials { width: 64px; height: 64px; flex: none; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 800; background: rgba(255, 255, 255, 0.14); box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.25); }
/* Color de la credencial según el nivel: plomo, azul y azul marino con plata */
.card_explorador { background: linear-gradient(135deg, #8A939E 0%, #5B636D 60%, #474E57 100%); }
.card_aliado { background: linear-gradient(135deg, #2F6FD6 0%, #1B4C9E 60%, #163C7A 100%); }
.card_premium { background: linear-gradient(135deg, #16294A 0%, #0A1628 60%, #1C2633 100%); box-shadow: 0 24px 48px rgba(10, 22, 40, 0.28), inset 0 0 0 2px #C9D1DB; }
.who { gap: 4px; }
.name { font-size: 26px; font-weight: 800; }
.org { font-size: 14px; color: var(--side-text); }
.facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.facts div { display: flex; flex-direction: column; gap: 2px; }
.facts span { font-size: 10px; letter-spacing: 0.12em; color: #9AA6B8; }
.facts strong { font-size: 13px; }
.qrBox { flex: 1 1 240px; align-items: flex-start; }
.small { font-size: 13px; display: inline-flex; align-items: center; gap: 6px; }
.members { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr)); }
.member { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius-lg); background: var(--surface); border: 1px solid var(--line); box-shadow: var(--shadow); }
.memberOff { opacity: 0.6; }
.memberAvatar { width: 40px; height: 40px; flex: none; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: var(--gradient-primary); color: #fff; font-size: 13px; font-weight: 800; }
.levels { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 210px), 1fr)); }
.level { transition: transform 0.25s var(--ease); border: 1px solid var(--line-2); border-radius: var(--radius); padding: 16px; display: flex; flex-direction: column; gap: 6px; background: var(--surface); }
.bloqueado { background: var(--surface-2); border-style: dashed; }
.bloqueado strong, .bloqueado .benefits { opacity: 0.6; }
.next { font-size: 15px; color: var(--navy-700); }
.swatch { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; vertical-align: -1px; }
.benefits { list-style: none; margin: 4px 0 0; padding: 0; display: flex; flex-direction: column; gap: 6px; }
.benefits li { display: flex; align-items: flex-start; gap: 6px; font-size: 13px; line-height: 1.45; color: var(--text-2); }
.benefits li :global(svg) { flex: none; margin-top: 2px; }
.actual { transform: translateY(-4px); box-shadow: 0 14px 30px rgba(15, 34, 64, 0.3); background: var(--gradient-primary); border-color: var(--navy-950); color: #fff; }
.actual strong { color: #fff; }
.pts { font-family: var(--mono); font-size: 12px; color: var(--muted); }
.actual .pts, .actual .tag, .actual .benefits li { color: var(--side-text); }
.tag { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 700; color: var(--navy-700); }
@media (max-width: 520px) { .card { aspect-ratio: auto; min-height: 220px; gap: 18px; } }
</style>
