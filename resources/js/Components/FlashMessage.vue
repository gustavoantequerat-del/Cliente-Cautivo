<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const hidden = ref(false);
const flash = computed(() => page.props.flash ?? {});
watch(() => [flash.value.success, flash.value.error], () => { hidden.value = false; });
</script>

<template>
    <div v-if="!hidden && (flash.success || flash.error)" :class="[$style.flash, flash.error ? $style.error : $style.success]" role="status">
        <span>{{ flash.error || flash.success }}</span>
        <button type="button" :class="$style.close" aria-label="Cerrar mensaje" @click="hidden = true">×</button>
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
