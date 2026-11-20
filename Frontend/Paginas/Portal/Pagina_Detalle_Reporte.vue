<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Componente_Visor_Pdf from '../../Componentes/Componente_Visor_Pdf.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

const Propiedades = defineProps({ Reporte: Object, Texto_Marca_Agua: String });
const Esta_Leido = ref(Propiedades.Reporte.Esta_Leido);

// Al llegar a la última página se marca como leído y se ganan las monedas (una sola vez)
function Marcar_Como_Leido() {
    if (Esta_Leido.value) return;
    Esta_Leido.value = true;
    router.post(`/research/${Propiedades.Reporte.Id}/leido`, {}, { preserveScroll: true, preserveState: true });
}
</script>

<template>
    <Head :title="Reporte.Titulo" />
    <Link href="/research" :class="$style.back">← Research</Link>

    <header :class="$style.head" class="card">
        <div class="row">
            <span class="badge badge-blue">{{ Reporte.Tema }}</span>
            <span v-if="Reporte.Es_Exclusivo" class="badge badge-navy">Exclusivo</span>
            <span v-if="Reporte.Fecha" class="muted" :class="$style.inline"><Componente_Icono Nombre="calendario" :Tamano="14" />{{ Reporte.Fecha }}</span>
            <span v-if="Reporte.Minutos_Lectura" class="muted" :class="$style.inline"><Componente_Icono Nombre="reloj" :Tamano="14" />{{ Reporte.Minutos_Lectura }} min</span>
        </div>
        <h1 :class="$style.title">{{ Reporte.Titulo }}</h1>
        <p v-if="Reporte.Resumen" class="muted">{{ Reporte.Resumen }}</p>
        <span :class="[$style.coins, Esta_Leido && $style.coinsDone]">
            <Componente_Icono :Nombre="Esta_Leido ? 'visto' : 'estrella'" :Tamano="15" />
            {{ Esta_Leido ? 'Leído · monedas ganadas' : `Llegue al final y gane ${Reporte.Monedas} monedas` }}
        </span>
    </header>

    <Componente_Visor_Pdf v-if="Reporte.Tiene_Archivo" :Url_Documento="Reporte.Url_Documento" :Texto_Marca_Agua="Texto_Marca_Agua" @Lectura_Completa="Marcar_Como_Leido" />
    <p v-else class="vacio">Este reporte aún no tiene documento.</p>
</template>

<style module>
.back { align-self: flex-start; font-weight: 700; font-size: 14px; text-decoration: none; padding: 6px 12px; border-radius: 999px; background: var(--surface); border: 1px solid var(--line); }
.head { display: flex; flex-direction: column; gap: 10px; max-width: 900px; width: 100%; align-self: center; }
.title { font-size: 26px; }
.inline { display: inline-flex; align-items: center; gap: 5px; font-size: 13px; }
.coins { align-self: flex-start; display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; padding: 6px 12px; border-radius: 999px; background: #FCEFD6; color: #8A5A0A; }
.coinsDone { background: var(--ok-bg); color: var(--ok-fg); }
</style>
