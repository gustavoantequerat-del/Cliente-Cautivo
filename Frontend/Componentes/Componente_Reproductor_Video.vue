<script setup>
/*
 * Reproductor BYOS: solo recibe la URL para incrustar construida en el Backend
 * (Drive /preview, YouTube nocookie, Vimeo player). Nunca HTML de terceros.
 */
defineProps({
    Url_Embebida: { type: String, default: null },
    Titulo: { type: String, required: true },
    Proveedor: { type: String, default: null },
});
</script>

<template>
    <div :class="$style.frame">
        <iframe v-if="Url_Embebida" :src="Url_Embebida" :title="Titulo" loading="lazy"
                allow="autoplay; fullscreen; picture-in-picture; encrypted-media" allowfullscreen
                referrerpolicy="strict-origin-when-cross-origin" />
        <div v-else :class="$style.empty">
            <strong>Video aún no disponible</strong>
            <span>El equipo de Blockfinity publicará el enlace en cuanto esté listo.</span>
        </div>
    </div>
    <p v-if="Url_Embebida && Proveedor === 'drive'" :class="$style.note">
        ¿No carga? Algunas redes corporativas bloquean Google Drive. Pruebe desde otra red o avise a su ejecutiva.
    </p>
</template>

<style module>
.frame {
  position: relative; width: 100%; aspect-ratio: 16 / 9; border-radius: var(--radius-lg); overflow: hidden;
  background: linear-gradient(135deg, var(--navy-700) 0%, var(--navy-950) 100%);
}
.frame iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
.empty {
  position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 6px; color: #fff; text-align: center; padding: 24px;
}
.empty span { color: var(--side-text); font-size: 14px; }
.note { margin-top: 8px; font-size: 12px; color: var(--muted); }
</style>
