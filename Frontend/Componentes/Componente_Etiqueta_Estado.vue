<script setup>
import { computed } from 'vue';

const Propiedades = defineProps({ Estado: { type: String, required: true } });

// Estado guardado en la base de datos → [texto visible, clase CSS]
const Estados_Conocidos = {
    aprobado: ['Aprobado', 'badge-ok'],
    publicado: ['Publicado', 'badge-blue'],
    pendiente_aprobacion: ['Requiere su aprobación', 'badge-navy'],
    borrador: ['En preparación', 'badge-outline'],
    pagada: ['Pagada', 'badge-ok'],
    pendiente: ['Pendiente', 'badge-warn'],
    vencida: ['Vencida', 'badge-danger'],
    anulada: ['Anulada', 'badge-outline'],
    en_atencion: ['En atención', 'badge-blue'],
    atendida: ['Atendida', 'badge-ok'],
    rechazada: ['Rechazada', 'badge-danger'],
    activo: ['En ejecución', 'badge-navy'],
    cerrado: ['Cerrado', 'badge-outline'],
};
const Texto_Y_Clase = computed(() => Estados_Conocidos[Propiedades.Estado] ?? [Propiedades.Estado, 'badge-outline']);
</script>

<template>
    <span :class="['badge', Texto_Y_Clase[1]]">{{ Texto_Y_Clase[0] }}</span>
</template>
