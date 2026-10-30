<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ Enlaces: { type: Array, default: () => [] } });
const Traducir_Etiqueta = (Etiqueta) => Etiqueta.replace('&laquo; Previous', '‹ Anterior').replace('Next &raquo;', 'Siguiente ›');
</script>

<template>
    <nav v-if="Enlaces.length > 3" :class="$style.nav" aria-label="Paginación">
        <template v-for="(Enlace, Posicion) in Enlaces" :key="Posicion">
            <Link v-if="Enlace.url" :href="Enlace.url" preserve-scroll :class="[$style.item, Enlace.active && $style.active]"
                  :aria-current="Enlace.active ? 'page' : undefined">{{ Traducir_Etiqueta(Enlace.label) }}</Link>
            <span v-else :class="[$style.item, $style.disabled]">{{ Traducir_Etiqueta(Enlace.label) }}</span>
        </template>
    </nav>
</template>

<style module>
.nav { display: flex; flex-wrap: wrap; gap: 6px; }
.item {
  min-width: 40px; min-height: 40px; padding: 0 12px; display: inline-flex; align-items: center; justify-content: center;
  border-radius: 10px; border: 1px solid var(--line); background: var(--surface); text-decoration: none;
  font-size: 14px; font-weight: 700; color: var(--navy-950);
}
.active { background: var(--navy-950); border-color: var(--navy-950); color: #fff; }
.disabled { color: var(--subtle); }
</style>
