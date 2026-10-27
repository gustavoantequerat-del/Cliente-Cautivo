<script setup>
import Icon from './Icon.vue';

defineProps({ deliverable: { type: Object, required: true } });
</script>

<template>
    <a v-if="deliverable.hasFile" :href="`/entregables/${deliverable.id}/descargar`" target="_blank" rel="noopener"
       class="btn btn-secondary btn-sm" :aria-label="`${deliverable.isExternal ? 'Abrir' : 'Descargar'} ${deliverable.title}`">
        <Icon :name="deliverable.isExternal ? 'external' : 'download'" :size="16" />{{ deliverable.isExternal ? 'Abrir' : 'Descargar' }}
    </a>
    <span v-else class="muted">—</span>
</template>
