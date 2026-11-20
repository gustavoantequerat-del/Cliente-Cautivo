<script setup>
/*
 * Calcula el código de verificación (SHA-256) de un archivo EN EL NAVEGADOR (Web Crypto).
 * No es una firma digital ni un dato biométrico: es un código que cambia si el archivo cambia.
 * El archivo nunca se envía al servidor.
 */
import { ref } from 'vue';
import Componente_Icono from './Componente_Icono.vue';

defineProps({
    Etiqueta: { type: String, default: 'Arrastre o seleccione un archivo' },
    Ayuda: { type: String, default: 'El archivo no se sube: se revisa en su equipo.' },
});
const Emitir = defineEmits(['Codigo_Calculado']);
const Esta_Calculando = ref(false);
const Nombre_Archivo = ref('');
const Esta_Arrastrando = ref(false);
const Id_Campo = `calculadora-codigo-${Math.random().toString(36).slice(2, 8)}`;

async function Calcular_Codigo(Archivo) {
    if (!Archivo) return;
    Esta_Calculando.value = true;
    Nombre_Archivo.value = Archivo.name;
    try {
        const Resumen = await crypto.subtle.digest('SHA-256', await Archivo.arrayBuffer());
        const Codigo_Hexadecimal = Array.from(new Uint8Array(Resumen)).map((Byte) => Byte.toString(16).padStart(2, '0')).join('');
        Emitir('Codigo_Calculado', Codigo_Hexadecimal);
    } finally {
        Esta_Calculando.value = false;
    }
}

const Al_Elegir_Archivo = (Evento) => Calcular_Codigo(Evento.target.files[0]);
const Al_Soltar_Archivo = (Evento) => { Esta_Arrastrando.value = false; Calcular_Codigo(Evento.dataTransfer.files[0]); };
</script>

<template>
    <label :for="Id_Campo" :class="[$style.drop, Esta_Arrastrando && $style.dragging]"
           @dragover.prevent="Esta_Arrastrando = true" @dragleave="Esta_Arrastrando = false" @drop.prevent="Al_Soltar_Archivo">
        <input :id="Id_Campo" type="file" class="sr-only" @change="Al_Elegir_Archivo">
        <span class="burbuja" :class="$style.icon"><Componente_Icono :Nombre="Esta_Calculando ? 'cargando' : 'codigo'" :class="Esta_Calculando && 'girando'" /></span>
        <strong>{{ Esta_Calculando ? 'Revisando…' : (Nombre_Archivo || Etiqueta) }}</strong>
        <span>{{ Ayuda }}</span>
    </label>
</template>

<style module>
.drop {
  transition: border-color 0.2s, background 0.2s, transform 0.2s var(--ease);
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; text-align: center;
  min-height: 110px; padding: 18px; border: 1.5px dashed var(--line-2); border-radius: var(--radius);
  background: var(--surface-2); cursor: pointer; font-size: 14px; color: var(--navy-950);
}
.drop span { font-size: 12px; color: var(--muted); }
.drop:focus-within { outline: 3px solid var(--navy-500); outline-offset: 2px; }
.drop:hover { border-color: var(--navy-500); background: var(--blue-50); }
.dragging { border-color: var(--navy-700); background: var(--blue-100); transform: scale(1.02); }
.icon { margin-bottom: 4px; transition: transform 0.3s var(--ease); }
.drop:hover .icon { transform: translateY(-3px); }
</style>
