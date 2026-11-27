<script setup>
/* Estado vacío con un ícono rodeado por una órbita animada (SVG): se entiende como "aún no hay nada aquí" sin texto largo */
import Componente_Icono from './Componente_Icono.vue';

defineProps({
    Icono: { type: String, default: 'info' },
    Texto: { type: String, required: true },
});
</script>

<template>
    <div class="vacio animar-entrada">
        <span :class="$style.orbita">
            <svg viewBox="0 0 96 96" width="96" height="96" aria-hidden="true" :class="$style.anillos">
                <circle cx="48" cy="48" r="44" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 7" :class="$style.anillo" />
                <circle cx="48" cy="4" r="4" fill="currentColor" :class="$style.satelite" />
            </svg>
            <span class="burbuja"><Componente_Icono :Nombre="Icono" :Tamano="26" /></span>
        </span>
        <strong>{{ Texto }}</strong>
        <slot />
    </div>
</template>

<style module>
.orbita { position: relative; display: inline-flex; align-items: center; justify-content: center; width: 96px; height: 96px; color: var(--line-2); }
.anillos { position: absolute; inset: 0; overflow: visible; }
.anillo, .satelite { transform-origin: 48px 48px; animation: orbitar 14s linear infinite; }
.satelite { color: var(--sky-400); animation-duration: 5s; }
@keyframes orbitar { to { rotate: 360deg; } }
@media (prefers-reduced-motion: reduce) { .anillo, .satelite { animation: none; } }
</style>
