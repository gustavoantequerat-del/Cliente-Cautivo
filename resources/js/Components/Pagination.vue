<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ links: { type: Array, default: () => [] } });
const label = (l) => l.replace('&laquo; Previous', '‹ Anterior').replace('Next &raquo;', 'Siguiente ›');
</script>

<template>
    <nav v-if="links.length > 3" :class="$style.nav" aria-label="Paginación">
        <template v-for="(l, i) in links" :key="i">
            <Link v-if="l.url" :href="l.url" preserve-scroll :class="[$style.item, l.active && $style.active]"
                  :aria-current="l.active ? 'page' : undefined">{{ label(l.label) }}</Link>
            <span v-else :class="[$style.item, $style.disabled]">{{ label(l.label) }}</span>
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
