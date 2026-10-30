<script setup>
/* Muestra un número que "cuenta" desde 0 hasta su valor al aparecer en pantalla */
import { onMounted, ref, watch } from 'vue';

const Propiedades = defineProps({
    Valor: { type: Number, required: true },
    Decimales: { type: Number, default: 0 },
});
const Valor_Mostrado = ref(0);
const Formatear_Numero = (Numero) => Numero.toLocaleString('es-BO', { minimumFractionDigits: 0, maximumFractionDigits: Propiedades.Decimales });

function Animar_Hasta(Valor_Final) {
    const Sin_Animacion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const Inicio = performance.now();
    const Duracion = 900;
    const Paso = (Ahora) => {
        const Progreso = Sin_Animacion ? 1 : Math.min(1, (Ahora - Inicio) / Duracion);
        Valor_Mostrado.value = Valor_Final * (1 - Math.pow(1 - Progreso, 3));
        if (Progreso < 1) requestAnimationFrame(Paso);
    };
    requestAnimationFrame(Paso);
}
onMounted(() => Animar_Hasta(Propiedades.Valor));
watch(() => Propiedades.Valor, Animar_Hasta);
</script>

<template>
    <span>{{ Formatear_Numero(Valor_Mostrado) }}</span>
</template>
