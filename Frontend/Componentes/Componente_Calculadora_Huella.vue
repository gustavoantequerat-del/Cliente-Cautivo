<script setup>
/*
 * Calcula la huella SHA-256 de un archivo EN EL NAVEGADOR (Web Crypto).
 * El archivo nunca se envía al servidor.
 */
import { ref } from 'vue';

defineProps({
    Etiqueta: { type: String, default: 'Arrastre o seleccione un archivo' },
    Ayuda: { type: String, default: 'El archivo no se sube: la huella se calcula en su equipo.' },
});
const Emitir = defineEmits(['Huella_Calculada']);
const Esta_Calculando = ref(false);
const Nombre_Archivo = ref('');
const Esta_Arrastrando = ref(false);
const Id_Campo = `calculadora-huella-${Math.random().toString(36).slice(2, 8)}`;

async function Calcular_Huella(Archivo) {
    if (!Archivo) return;
    Esta_Calculando.value = true;
    Nombre_Archivo.value = Archivo.name;
    try {
        const Resumen = await crypto.subtle.digest('SHA-256', await Archivo.arrayBuffer());
        const Huella_Hexadecimal = Array.from(new Uint8Array(Resumen)).map((Byte) => Byte.toString(16).padStart(2, '0')).join('');
        Emitir('Huella_Calculada', Huella_Hexadecimal);
    } finally {
        Esta_Calculando.value = false;
    }
}

const Al_Elegir_Archivo = (Evento) => Calcular_Huella(Evento.target.files[0]);
const Al_Soltar_Archivo = (Evento) => { Esta_Arrastrando.value = false; Calcular_Huella(Evento.dataTransfer.files[0]); };
</script>

<template>
    <label :for="Id_Campo" :class="[$style.drop, Esta_Arrastrando && $style.dragging]"
           @dragover.prevent="Esta_Arrastrando = true" @dragleave="Esta_Arrastrando = false" @drop.prevent="Al_Soltar_Archivo">
        <input :id="Id_Campo" type="file" class="sr-only" @change="Al_Elegir_Archivo">
        <strong>{{ Esta_Calculando ? 'Calculando huella…' : (Nombre_Archivo || Etiqueta) }}</strong>
        <span>{{ Ayuda }}</span>
    </label>
</template>

<style module>
.drop {
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; text-align: center;
  min-height: 110px; padding: 18px; border: 1.5px dashed var(--line-2); border-radius: var(--radius);
  background: var(--surface-2); cursor: pointer; font-size: 14px; color: var(--navy-950);
}
.drop span { font-size: 12px; color: var(--muted); }
.drop:focus-within { outline: 3px solid var(--navy-500); outline-offset: 2px; }
.dragging { border-color: var(--navy-700); background: var(--blue-100); }
</style>
