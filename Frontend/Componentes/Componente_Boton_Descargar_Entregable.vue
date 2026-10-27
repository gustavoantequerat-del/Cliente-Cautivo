<script setup>
import Componente_Icono from './Componente_Icono.vue';

defineProps({ Entregable: { type: Object, required: true } });
</script>

<template>
    <a v-if="Entregable.Tiene_Archivo" :href="`/entregables/${Entregable.Id}/descargar`" target="_blank" rel="noopener"
       class="btn btn-secondary btn-sm" :aria-label="`${Entregable.Es_Enlace_Externo ? 'Abrir' : 'Descargar'} ${Entregable.Titulo}`">
        <Componente_Icono :Nombre="Entregable.Es_Enlace_Externo ? 'externo' : 'descargar'" :Tamano="16" />{{ Entregable.Es_Enlace_Externo ? 'Abrir' : 'Descargar' }}
    </a>
    <span v-else class="muted">—</span>
</template>
