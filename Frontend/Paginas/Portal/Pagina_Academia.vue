<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Paginacion from '../../Componentes/Componente_Paginacion.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

const Propiedades = defineProps({ Proximo_En_Vivo: Object, Videos: Object, Mi_Avance: Object, Formatos: Object, Temas: Array, Filtros_Aplicados: Object });

function Alternar_Filtro(Nombre_Filtro, Valor) {
    const Filtros = { formato: Propiedades.Filtros_Aplicados.Formato, tema: Propiedades.Filtros_Aplicados.Tema };
    Filtros[Nombre_Filtro] = Filtros[Nombre_Filtro] === Valor ? null : Valor;
    router.get('/academia', Object.fromEntries(Object.entries(Filtros).filter(([, Valor_Filtro]) => Valor_Filtro)), { preserveScroll: true });
}
const Inscribirse_Webinar = (Id_Video) => router.post(`/academia/${Id_Video}/inscribirme`, {}, { preserveScroll: true });
</script>

<template>
    <Head title="Píldoras y webinars" />
    <Componente_Encabezado_Pagina Titulo="Píldoras y webinars" Introduccion="Formación continua para cada área: webinars en vivo, grabaciones y píldoras de pocos minutos." />

    <div :class="$style.top">
        <section v-if="Proximo_En_Vivo" :class="$style.live">
            <span :class="$style.kicker"><span :class="$style.pulse" />Webinar en vivo</span>
            <span :class="$style.when">{{ Proximo_En_Vivo.Fecha_Programada }}<template v-if="Proximo_En_Vivo.Duracion_Minutos"> · {{ Proximo_En_Vivo.Duracion_Minutos }} min</template></span>
            <h2 :class="$style.liveTitle">{{ Proximo_En_Vivo.Titulo }}</h2>
            <span :class="$style.speaker">{{ Proximo_En_Vivo.Expositor }}</span>
            <div class="row">
                <button v-if="!Proximo_En_Vivo.Esta_Inscrito" type="button" :class="$style.white" @click="Inscribirse_Webinar(Proximo_En_Vivo.Id)">Reservar mi lugar</button>
                <span v-else :class="$style.done"><Componente_Icono Nombre="visto" :Tamano="16" />Inscrito</span>
                <a :href="`/academia/${Proximo_En_Vivo.Id}/calendario.ics`" :class="$style.ghost"><Componente_Icono Nombre="calendario" :Tamano="16" />Añadir al calendario</a>
            </div>
        </section>
        <aside class="card stack" :class="$style.stats">
            <span class="eyebrow">Mi avance</span>
            <div class="row"><strong :class="$style.big">{{ String(Mi_Avance.Horas).replace('.', ',') }} h</strong><span class="muted">de formación</span></div>
            <div class="row"><strong :class="$style.big">{{ Mi_Avance.Completados }}</strong><span class="muted">contenidos completados</span></div>
            <Link href="/credencial" class="link-arrow">Ver mis puntos de alianza →</Link>
        </aside>
    </div>

    <div class="stack">
        <div class="chips" role="group" aria-label="Formato">
            <button v-for="(Nombre_Formato, Clave_Formato) in Formatos" :key="Clave_Formato" type="button" class="chip"
                    :aria-pressed="Filtros_Aplicados.Formato === Clave_Formato ? 'true' : 'false'" @click="Alternar_Filtro('formato', Clave_Formato)">{{ Nombre_Formato }}</button>
        </div>
        <div class="chips" role="group" aria-label="Tema">
            <button v-for="Tema in Temas" :key="Tema" type="button" class="chip"
                    :aria-pressed="Filtros_Aplicados.Tema === Tema ? 'true' : 'false'" @click="Alternar_Filtro('tema', Tema)">{{ Tema }}</button>
        </div>
    </div>

    <div class="grid-3">
        <Link v-for="Video in Videos.data" :key="Video.Id" :href="`/academia/${Video.Id}`" :class="$style.video">
            <div :class="[$style.thumb, $style[Video.Formato]]" :style="Video.Miniatura ? { backgroundImage: `url(${Video.Miniatura})` } : {}">
                <span :class="$style.kind">{{ Video.Nombre_Formato }}</span>
                <span v-if="Video.Duracion_Minutos" :class="$style.dur">{{ Video.Duracion_Minutos }} min</span>
                <Componente_Icono v-if="!Video.Miniatura" Nombre="reproducir" :Tamano="40" :Grosor="1.4" />
            </div>
            <div :class="$style.body">
                <span class="eyebrow">{{ Video.Tema }}</span>
                <strong :class="$style.vtitle">{{ Video.Titulo }}</strong>
                <span class="muted" :class="$style.small">{{ Video.Expositor }}</span>
                <div class="row between" :class="$style.meta">
                    <span v-if="Video.Esta_Completado" class="badge badge-ok"><Componente_Icono Nombre="visto" :Tamano="14" />Completado</span>
                    <span v-else-if="Video.Es_Proximo" class="badge badge-navy">{{ Video.Fecha_Programada }}</span>
                    <span v-else class="badge badge-outline">+{{ Video.Puntos }} pts</span>
                </div>
            </div>
        </Link>
    </div>
    <p v-if="!Videos.data.length" class="card muted">No hay contenidos con estos filtros.</p>
    <Componente_Paginacion :Enlaces="Videos.links" />
</template>

<style module>
.top { display: flex; flex-wrap: wrap; gap: 20px; }
.live { flex: 999 1 520px; background: var(--navy-950); color: #fff; border-radius: var(--radius-lg); padding: 28px; display: flex; flex-direction: column; gap: 10px; }
.kicker { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: var(--side-label); }
.pulse { width: 8px; height: 8px; border-radius: 50%; background: #FF6B5E; }
.when { color: var(--side-text); font-size: 14px; text-transform: capitalize; }
.liveTitle { color: #fff; font-size: 24px; }
.speaker { color: var(--side-label); font-size: 14px; }
.white { min-height: 46px; padding: 0 18px; border-radius: 10px; border: 0; background: #fff; color: var(--navy-950); font-weight: 800; cursor: pointer; }
.ghost { display: inline-flex; align-items: center; gap: 8px; min-height: 46px; padding: 0 16px; border-radius: 10px; border: 1px solid var(--navy-500); color: #fff; text-decoration: none; font-weight: 700; font-size: 14px; }
.ghost:hover { color: #fff; background: var(--navy-800); }
.done { display: inline-flex; align-items: center; gap: 6px; min-height: 46px; padding: 0 14px; border-radius: 10px; background: var(--navy-800); font-weight: 700; }
.stats { flex: 1 1 260px; }
.big { font-size: 26px; font-weight: 800; color: var(--navy-950); }
.video { background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-lg); overflow: hidden; text-decoration: none; color: var(--ink); display: flex; flex-direction: column; }
.video:hover { border-color: var(--navy-700); color: var(--ink); }
.thumb {
  position: relative; aspect-ratio: 16 / 9; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.85);
  background-size: cover; background-position: center; background-color: var(--navy-800);
}
/* Un fondo por formato de video (en_vivo, grabado, pildora) */
.en_vivo { background-image: linear-gradient(135deg, var(--navy-700) 0%, var(--navy-950) 100%); }
.grabado { background-image: linear-gradient(135deg, #3B5B8C 0%, #16305A 100%); }
.pildora { background-image: linear-gradient(135deg, #5F6773 0%, #2B3038 100%); }
.kind { position: absolute; top: 10px; left: 10px; font-size: 11px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; background: #fff; color: var(--navy-950); padding: 3px 8px; border-radius: 6px; }
.dur { position: absolute; bottom: 10px; right: 10px; font-size: 12px; font-weight: 700; background: rgba(10,22,40,0.8); color: #fff; padding: 2px 8px; border-radius: 6px; }
.body { padding: 16px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
.vtitle { font-size: 16px; line-height: 1.35; color: var(--navy-950); }
.small { font-size: 13px; }
.meta { margin-top: auto; padding-top: 6px; }
</style>
