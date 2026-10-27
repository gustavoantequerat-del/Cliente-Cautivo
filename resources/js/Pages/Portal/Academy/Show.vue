<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import VideoPlayer from '../../../Components/VideoPlayer.vue';
import Icon from '../../../Components/Icon.vue';

const props = defineProps({ video: Object });
const complete = () => router.post(`/academia/${props.video.id}/visto`, {}, { preserveScroll: true });
const register = () => router.post(`/academia/${props.video.id}/inscribirme`, {}, { preserveScroll: true });
</script>

<template>
    <Head :title="video.title" />
    <Link href="/academia" :class="$style.back">← Píldoras y webinars</Link>

    <div :class="$style.layout">
        <div class="stack" :class="$style.main">
            <VideoPlayer :src="video.embedUrl" :title="video.title" :provider="video.provider" />
            <div class="stack" :class="$style.text">
                <div class="row">
                    <span class="badge badge-navy">{{ video.kindLabel }}</span>
                    <span v-if="video.topic" class="badge badge-outline">{{ video.topic }}</span>
                    <span v-if="video.duration" class="muted">{{ video.duration }} min</span>
                </div>
                <h1 :class="$style.title">{{ video.title }}</h1>
                <span v-if="video.speaker" class="muted">{{ video.speaker }}</span>
                <p v-if="video.description" :class="$style.desc">{{ video.description }}</p>
            </div>
        </div>
        <aside class="card stack" :class="$style.side">
            <template v-if="video.upcoming">
                <span class="eyebrow">En vivo</span>
                <strong :class="$style.when">{{ video.scheduledAt }}</strong>
                <button v-if="!video.registered" type="button" class="btn btn-primary" @click="register">Reservar mi lugar</button>
                <span v-else class="badge badge-ok"><Icon name="check" :size="14" />Inscrito</span>
                <a :href="`/academia/${video.id}/calendario.ics`" class="btn btn-secondary"><Icon name="calendar" :size="16" />Añadir al calendario</a>
                <a v-if="video.liveUrl && video.registered" :href="video.liveUrl" target="_blank" rel="noopener" class="btn btn-secondary">
                    <Icon name="external" :size="16" />Enlace a la sala
                </a>
            </template>
            <span class="eyebrow">Su progreso</span>
            <template v-if="video.completed">
                <span class="badge badge-ok"><Icon name="check" :size="14" />Completado</span>
                <p class="muted">Ya sumó este contenido a su avance.</p>
            </template>
            <template v-else>
                <p class="muted">Al terminar, márquelo como visto: suma <strong>{{ video.points }} puntos</strong> de alianza para su institución.</p>
                <button type="button" class="btn btn-secondary" :disabled="!video.embedUrl" @click="complete">
                    <Icon name="check" :size="16" />Marcar como visto
                </button>
            </template>
        </aside>
    </div>
</template>

<style module>
.back { font-weight: 700; font-size: 14px; text-decoration: none; }
.layout { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; }
.main { flex: 999 1 600px; min-width: 0; }
.side { flex: 1 1 280px; min-width: 0; }
.text { gap: 10px; }
.title { font-size: 26px; }
.desc { font-size: 16px; color: var(--text-2); line-height: 1.6; white-space: pre-line; }
.when { font-size: 17px; text-transform: capitalize; }
</style>
