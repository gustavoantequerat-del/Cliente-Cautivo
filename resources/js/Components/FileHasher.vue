<script setup>
/*
 * Calcula la huella SHA-256 de un archivo EN EL NAVEGADOR (Web Crypto).
 * El archivo nunca se envía al servidor.
 */
import { ref } from 'vue';

const props = defineProps({
    label: { type: String, default: 'Arrastre o seleccione un archivo' },
    hint: { type: String, default: 'El archivo no se sube: la huella se calcula en su equipo.' },
});
const emit = defineEmits(['hashed']);
const busy = ref(false);
const fileName = ref('');
const dragging = ref(false);
const inputId = `hasher-${Math.random().toString(36).slice(2, 8)}`;

async function hashFile(file) {
    if (!file) return;
    busy.value = true;
    fileName.value = file.name;
    try {
        const buffer = await file.arrayBuffer();
        const digest = await crypto.subtle.digest('SHA-256', buffer);
        const hex = Array.from(new Uint8Array(digest)).map((b) => b.toString(16).padStart(2, '0')).join('');
        emit('hashed', { hash: hex, name: file.name });
    } finally {
        busy.value = false;
    }
}

const onChange = (e) => hashFile(e.target.files[0]);
const onDrop = (e) => { dragging.value = false; hashFile(e.dataTransfer.files[0]); };
</script>

<template>
    <label :for="inputId" :class="[$style.drop, dragging && $style.dragging]"
           @dragover.prevent="dragging = true" @dragleave="dragging = false" @drop.prevent="onDrop">
        <input :id="inputId" type="file" class="sr-only" @change="onChange">
        <strong>{{ busy ? 'Calculando huella…' : (fileName || props.label) }}</strong>
        <span>{{ props.hint }}</span>
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
