<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PageHeader from '../../../Components/PageHeader.vue';
import Pagination from '../../../Components/Pagination.vue';
import Icon from '../../../Components/Icon.vue';

const props = defineProps({ nextLive: Object, videos: Object, stats: Object, kinds: Object, topics: Array, filters: Object });

function filter(key, value) {
    const params = { ...props.filters, [key]: props.filters[key] === value ? null : value };
    router.get('/academia', Object.fromEntries(Object.entries(params).filter(([, v]) => v)), { preserveScroll: true });
}
const thumbClass = { live: 'thumbLive', recorded: 'thumbRecorded', pill: 'thumbPill' };
const register = (id) => router.post(`/academia/${id}/inscribirme`, {}, { preserveScroll: true });
</script>

<template>
    <Head title="Píldoras y webinars" />
    <PageHeader title="Píldoras y webinars" intro="Formación continua para cada área: webinars en vivo, grabaciones y píldoras de pocos minutos." />

    <div :class="$style.top">
        <section v-if="nextLive" :class="$style.live">
            <span :class="$style.kicker"><span :class="$style.pulse" />Webinar en vivo</span>
            <span :class="$style.when">{{ nextLive.scheduledAt }}<template v-if="nextLive.duration"> · {{ nextLive.duration }} min</template></span>
            <h2 :class="$style.liveTitle">{{ nextLive.title }}</h2>
            <span :class="$style.speaker">{{ nextLive.speaker }}</span>
            <div class="row">
                <button v-if="!nextLive.registered" type="button" :class="$style.white" @click="register(nextLive.id)">Reservar mi lugar</button>
                <span v-else :class="$style.done"><Icon name="check" :size="16" />Inscrito</span>
                <a :href="`/academia/${nextLive.id}/calendario.ics`" :class="$style.ghost"><Icon name="calendar" :size="16" />Añadir al calendario</a>
            </div>
        </section>
        <aside class="card stack" :class="$style.stats">
            <span class="eyebrow">Mi avance</span>
            <div class="row"><strong :class="$style.big">{{ String(stats.hours).replace('.', ',') }} h</strong><span class="muted">de formación</span></div>
            <div class="row"><strong :class="$style.big">{{ stats.completed }}</strong><span class="muted">contenidos completados</span></div>
            <Link href="/credencial" class="link-arrow">Ver mis puntos de alianza →</Link>
        </aside>
    </div>

    <div class="stack">
        <div class="chips" role="group" aria-label="Formato">
            <button v-for="(label, key) in kinds" :key="key" type="button" class="chip" :aria-pressed="filters.formato === key ? 'true' : 'false'" @click="filter('formato', key)">{{ label }}</button>
        </div>
        <div class="chips" role="group" aria-label="Tema">
            <button v-for="t in topics" :key="t" type="button" class="chip" :aria-pressed="filters.tema === t ? 'true' : 'false'" @click="filter('tema', t)">{{ t }}</button>
        </div>
    </div>

    <div class="grid-3">
        <Link v-for="v in videos.data" :key="v.id" :href="`/academia/${v.id}`" :class="$style.video">
            <div :class="[$style.thumb, $style[thumbClass[v.kind]]]" :style="v.thumbnail ? { backgroundImage: `url(${v.thumbnail})` } : {}">
                <span :class="$style.kind">{{ v.kindLabel }}</span>
                <span v-if="v.duration" :class="$style.dur">{{ v.duration }} min</span>
                <Icon v-if="!v.thumbnail" name="play" :size="40" :stroke="1.4" />
            </div>
            <div :class="$style.body">
                <span class="eyebrow">{{ v.topic }}</span>
                <strong :class="$style.vtitle">{{ v.title }}</strong>
                <span class="muted" :class="$style.small">{{ v.speaker }}</span>
                <div class="row between" :class="$style.meta">
                    <span v-if="v.completed" class="badge badge-ok"><Icon name="check" :size="14" />Completado</span>
                    <span v-else-if="v.upcoming" class="badge badge-navy">{{ v.scheduledAt }}</span>
                    <span v-else class="badge badge-outline">+{{ v.points }} pts</span>
                </div>
            </div>
        </Link>
    </div>
    <p v-if="!videos.data.length" class="card muted">No hay contenidos con estos filtros.</p>
    <Pagination :links="videos.links" />
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
.thumbLive { background-image: linear-gradient(135deg, var(--navy-700) 0%, var(--navy-950) 100%); }
.thumbRecorded { background-image: linear-gradient(135deg, #3B5B8C 0%, #16305A 100%); }
.thumbPill { background-image: linear-gradient(135deg, #5F6773 0%, #2B3038 100%); }
.kind { position: absolute; top: 10px; left: 10px; font-size: 11px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; background: #fff; color: var(--navy-950); padding: 3px 8px; border-radius: 6px; }
.dur { position: absolute; bottom: 10px; right: 10px; font-size: 12px; font-weight: 700; background: rgba(10,22,40,0.8); color: #fff; padding: 2px 8px; border-radius: 6px; }
.body { padding: 16px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
.vtitle { font-size: 16px; line-height: 1.35; color: var(--navy-950); }
.small { font-size: 13px; }
.meta { margin-top: auto; padding-top: 6px; }
</style>
