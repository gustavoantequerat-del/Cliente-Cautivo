<script setup>
import { computed } from 'vue';

const props = defineProps({ status: { type: String, required: true } });

const MAP = {
    approved: ['Aprobado', 'badge-ok'],
    published: ['Publicado', 'badge-blue'],
    pending_approval: ['Requiere su aprobación', 'badge-navy'],
    draft: ['En preparación', 'badge-outline'],
    paid: ['Pagada', 'badge-ok'],
    pending: ['Pendiente', 'badge-warn'],
    overdue: ['Vencida', 'badge-danger'],
    void: ['Anulada', 'badge-outline'],
    in_progress: ['En atención', 'badge-blue'],
    done: ['Atendida', 'badge-ok'],
    rejected: ['Rechazada', 'badge-danger'],
    active: ['En ejecución', 'badge-navy'],
    closed: ['Cerrado', 'badge-outline'],
};
const info = computed(() => MAP[props.status] ?? [props.status, 'badge-outline']);
</script>

<template>
    <span :class="['badge', info[1]]">{{ info[0] }}</span>
</template>
