<script setup>
/* PDF con el visor de Google Drive. Documento = { Url_Visor, Url_Abrir } armado en el Backend (Servicio_Google_Drive). */
defineProps({
    Documento: { type: Object, required: true },
    Titulo: { type: String, required: true },
});
</script>

<template>
    <section :class="$style.visor">
        <iframe :src="Documento.Url_Visor" :title="Titulo" loading="lazy" allow="fullscreen" allowfullscreen referrerpolicy="strict-origin-when-cross-origin" />
        <p :class="$style.nota">
            ¿No carga? Puede que su red bloquee Google Drive.
            <a :href="Documento.Url_Abrir" target="_blank" rel="noopener">Abrir en Google Drive</a>
        </p>
    </section>
</template>

<style module>
.visor { display: flex; flex-direction: column; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--line); box-shadow: var(--shadow); background: var(--surface); }
.visor iframe { width: 100%; height: min(85vh, 1000px); border: 0; background: #F1F3F4; }
.nota { padding: 10px 14px; font-size: 13px; color: var(--muted); }
.nota a { font-weight: 700; }
@media (max-width: 700px) { .visor iframe { height: 75vh; } }
</style>
