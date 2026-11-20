<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Componente_Reproductor_Video from '../../Componentes/Componente_Reproductor_Video.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

const Propiedades = defineProps({ Video: Object });
const Marcar_Como_Visto = () => router.post(`/academia/${Propiedades.Video.Id}/visto`, {}, { preserveScroll: true });
const Inscribirse_Webinar = () => router.post(`/academia/${Propiedades.Video.Id}/inscribirme`, {}, { preserveScroll: true });
</script>

<template>
    <Head :title="Video.Titulo" />
    <Link href="/academia" :class="$style.back">← Academia</Link>

    <div :class="$style.layout">
        <div class="stack" :class="$style.main">
            <Componente_Reproductor_Video :Url_Embebida="Video.Url_Embebida" :Titulo="Video.Titulo" :Proveedor="Video.Proveedor" />
            <div class="stack" :class="$style.text">
                <div class="row">
                    <span class="badge badge-navy">{{ Video.Nombre_Formato }}</span>
                    <span v-if="Video.Tema" class="badge badge-outline">{{ Video.Tema }}</span>
                    <span v-if="Video.Duracion_Minutos" class="muted">{{ Video.Duracion_Minutos }} min</span>
                </div>
                <h1 :class="$style.title">{{ Video.Titulo }}</h1>
                <span v-if="Video.Expositor" class="muted">{{ Video.Expositor }}</span>
                <p v-if="Video.Descripcion" :class="$style.desc">{{ Video.Descripcion }}</p>
            </div>
        </div>
        <aside class="card stack" :class="$style.side">
            <template v-if="Video.Es_Proximo">
                <span class="eyebrow">En vivo</span>
                <strong :class="$style.when">{{ Video.Fecha_Programada }}</strong>
                <button v-if="!Video.Esta_Inscrito" type="button" class="btn btn-primary" @click="Inscribirse_Webinar">Reservar mi lugar</button>
                <span v-else class="badge badge-ok"><Componente_Icono Nombre="visto" :Tamano="14" />Inscrito</span>
                <a :href="`/academia/${Video.Id}/calendario.ics`" class="btn btn-secondary"><Componente_Icono Nombre="calendario" :Tamano="16" />Añadir al calendario</a>
                <a v-if="Video.Enlace_Sala && Video.Esta_Inscrito" :href="Video.Enlace_Sala" target="_blank" rel="noopener" class="btn btn-secondary">
                    <Componente_Icono Nombre="externo" :Tamano="16" />Enlace a la sala
                </a>
            </template>
            <span class="eyebrow">Su progreso</span>
            <template v-if="Video.Esta_Completado">
                <span class="badge badge-ok"><Componente_Icono Nombre="visto" :Tamano="14" />Completado</span>
            </template>
            <template v-else>
                <span class="badge badge-blue" :class="$style.points"><Componente_Icono Nombre="estrella" :Tamano="14" />+{{ Video.Puntos }} monedas al terminar</span>
                <button type="button" class="btn btn-secondary" :disabled="!Video.Url_Embebida" @click="Marcar_Como_Visto">
                    <Componente_Icono Nombre="visto" :Tamano="16" />Marcar como visto
                </button>
            </template>
        </aside>
    </div>
</template>

<style module>
.back { align-self: flex-start; font-weight: 700; font-size: 14px; text-decoration: none; padding: 6px 12px; border-radius: 999px; background: var(--surface); border: 1px solid var(--line); }
.layout { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; }
.main { flex: 999 1 600px; min-width: 0; }
.side { flex: 1 1 280px; min-width: 0; }
.text { gap: 10px; }
.title { font-size: 26px; }
.desc { font-size: 16px; color: var(--text-2); line-height: 1.6; white-space: pre-line; }
.when { font-size: 17px; text-transform: capitalize; }
.points { align-self: flex-start; }
</style>
