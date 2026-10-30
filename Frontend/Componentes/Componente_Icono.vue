<script setup>
import { computed } from 'vue';

const Propiedades = defineProps({
    Nombre: { type: String, required: true },
    Tamano: { type: [Number, String], default: 20 },
    Grosor: { type: [Number, String], default: 1.8 },
});

// Iconos de trazo (mismos trazos del diseño)
const Trazos_Iconos = {
    inicio: 'M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z',
    proyectos: 'M3 7h18v13H3zM8 7V4h8v3',
    boveda: 'M3 4h18v5H3zM5 9v11h14V9M10 13h4',
    facturacion: 'M6 2h12v20l-3-2-3 2-3-2-3 2zM9 7h6M9 11h6M9 15h4',
    reloj: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM12 6v6l4 2',
    grafico: 'M4 20V10M10 20V4M16 20v-7M2 20h20',
    reproducir: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM10 8l6 4-6 4z',
    credencial: 'M12 2l8 4.5v9L12 20l-8-4.5v-9zM9 11l2 2 4-4',
    salir: 'M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9',
    visto: 'M20 6L9 17l-5-5',
    descargar: 'M12 3v12M7 10l5 5 5-5M4 21h16',
    externo: 'M14 4h6v6M20 4l-9 9M18 14v6H4V6h6',
    calendario: 'M4 5h16v16H4zM4 10h16M9 3v4M15 3v4',
    archivo: 'M6 3h9l4 4v14H6zM14 3v5h5',
    menu: 'M4 6h16M4 12h16M4 18h16',
    cerrar: 'M6 6l12 12M18 6L6 18',
    agregar: 'M12 5v14M5 12h14',
    cuadricula: 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z',
    huella: 'M12 11v4M8 21c1-2 1.5-4 1.5-7a2.5 2.5 0 0 1 5 0c0 1.5-.2 3-.6 4.4M5 17c.7-1.6 1-3.3 1-5a6 6 0 0 1 12 0c0 .8 0 1.6-.1 2.3M17 20c.3-.8.6-1.7.8-2.6',
    ojo: 'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z',
    ojo_tachado: 'M3 3l18 18M10.6 5.1A9.8 9.8 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.5 7 10 7a9.6 9.6 0 0 0 5.4-1.6M9.9 9.9a3 3 0 0 0 4.2 4.2',
    escudo: 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6zM9 12l2 2 4-4',
    candado: 'M6 11h12v10H6zM8 11V7a4 4 0 0 1 8 0v4',
    usuario: 'M20 21a8 8 0 0 0-16 0M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10z',
    usuarios: 'M17 21a6 6 0 0 0-12 0M11 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM22 21a5 5 0 0 0-4-4.9M16 3.1a4 4 0 0 1 0 7.8',
    edificio: 'M4 21V5l8-3 8 3v16M4 21h16M9 9h1M14 9h1M9 13h1M14 13h1M10 21v-4h4v4',
    capas: 'M12 3l9 5-9 5-9-5zM3 13l9 5 9-5',
    libro: 'M4 4h11a3 3 0 0 1 3 3v14H7a3 3 0 0 1-3-3zM4 18a3 3 0 0 1 3-3h11',
    lista: 'M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01',
    flecha: 'M5 12h14M13 6l6 6-6 6',
    buscar: 'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM21 21l-5-5',
    lapiz: 'M4 20h4L19 9l-4-4L4 16zM14 6l4 4',
    basura: 'M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3',
    alerta: 'M12 3l10 18H2zM12 10v5M12 18h.01',
    info: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM12 11v6M12 7h.01',
    correo: 'M3 6h18v12H3zM3 7l9 6 9-6',
    estrella: 'M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z',
    dinero: 'M3 7h18v10H3zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM6 10v.01M18 14v.01',
    rayo: 'M13 2L4 14h7l-1 8 9-12h-7z',
    cargando: 'M12 3a9 9 0 1 0 9 9',
};

const Trazo = computed(() => Trazos_Iconos[Propiedades.Nombre] ?? Trazos_Iconos.archivo);
</script>

<template>
    <svg :width="Tamano" :height="Tamano" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         :stroke-width="Grosor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path :d="Trazo" />
    </svg>
</template>
