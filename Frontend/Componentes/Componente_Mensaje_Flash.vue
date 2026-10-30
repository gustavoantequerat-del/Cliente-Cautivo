<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const Pagina = usePage();
const Esta_Oculto = ref(false);
const Mensajes = computed(() => Pagina.props.Mensajes_Flash ?? {});
watch(() => [Mensajes.value.Exito, Mensajes.value.Error], () => { Esta_Oculto.value = false; });
</script>

<template>
    <div v-if="!Esta_Oculto && (Mensajes.Exito || Mensajes.Error)" :class="[$style.flash, Mensajes.Error ? $style.error : $style.success]" role="status">
        <span>{{ Mensajes.Error || Mensajes.Exito }}</span>
        <button type="button" :class="$style.close" aria-label="Cerrar mensaje" @click="Esta_Oculto = true">×</button>
    </div>
</template>

<style module>
.flash {
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  padding: 12px 16px; border-radius: 12px; font-size: 14px; font-weight: 600;
}
.success { background: var(--ok-bg); color: var(--ok-fg); }
.error { background: var(--danger-bg); color: var(--danger-fg); }
.close { background: none; border: 0; font-size: 22px; line-height: 1; cursor: pointer; color: inherit; width: 32px; height: 32px; }
</style>
