<script setup>
/* Marca provisional. Reemplace por el logo oficial si lo prefiere. */
defineProps({ Fondo_Claro: { type: Boolean, default: false }, Compacta: { type: Boolean, default: false } });
</script>

<template>
    <span :class="[$style.mark, Fondo_Claro && $style.dark, Compacta && $style.compact]">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
             stroke-linejoin="round" aria-hidden="true"><path d="M12 2l8 4.5v9L12 20l-8-4.5v-9zM12 2v18M4 6.5l16 9M20 6.5l-16 9" /></svg>
        <span :class="$style.text"><strong>Blockfinity</strong><span>Advisors</span></span>
    </span>
</template>

<style module>
.mark { display: inline-flex; align-items: center; gap: 10px; color: #fff; }
.dark { color: var(--navy-950); }
.text { display: flex; flex-direction: column; line-height: 1.05; }
.text strong { font-size: 18px; font-weight: 800; letter-spacing: -0.01em; }
.text span { font-size: 11px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; opacity: 0.75; }
.compact { gap: 6px; }
.compact svg { width: 20px; height: 20px; }
.compact .text strong { font-size: 13px; }
.compact .text span { font-size: 8px; }
</style>
