<script setup>
/*
 * PDF dentro del portal. Documento = { Url_Visor, Url_Abrir } armado en el Backend (Servicio_Documentos_Pdf):
 * el archivo subido se ve con el lector de PDF del navegador; un enlace de Drive, con el visor de Google.
 * En celulares el lector incrustado a veces no se muestra: por eso siempre está el botón para abrirlo.
 */
defineProps({
    Documento: { type: Object, required: true },
    Titulo: { type: String, required: true },
});
</script>

<template>
    <section :class="$style.visor">
        <iframe :src="Documento.Url_Visor" :title="Titulo" loading="lazy" allow="fullscreen" allowfullscreen />
        <div :class="$style.bar">
            <span class="muted">¿No se ve el documento?</span>
            <a :href="Documento.Url_Abrir" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">Abrir el PDF</a>
        </div>
    </section>
</template>

<style module>
.visor { display: flex; flex-direction: column; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--line); box-shadow: var(--shadow); background: var(--surface); }
.visor iframe { width: 100%; height: min(85vh, 1000px); border: 0; background: #525659; }
.bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 14px; font-size: 13px; }
@media (max-width: 700px) { .visor iframe { height: 70vh; } }
</style>
