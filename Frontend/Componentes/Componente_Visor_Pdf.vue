<script setup>
/*
 * Visor de reportes: dibuja cada página del PDF en un <canvas> con PDF.js (el texto queda como imagen,
 * no se puede seleccionar ni copiar) y pinta encima una marca de agua diagonal. Sin botón de descarga,
 * sin clic derecho y sin impresión. Ojo: esto desalienta copiar, pero quien sepa usar las herramientas
 * del navegador puede capturar la pantalla; la marca de agua con el nombre del usuario sirve para rastrear filtraciones.
 */
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import Url_Trabajador_Pdf from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import Componente_Icono from './Componente_Icono.vue';

const Propiedades = defineProps({
    Url_Documento: { type: String, required: true },
    Texto_Marca_Agua: { type: String, default: '' },
});
const Emitir = defineEmits(['Lectura_Completa']);

const Estado = ref('cargando'); // cargando | listo | error
const Cantidad_Paginas = ref(0);
const Proporcion_Pagina = ref(1.414); // alto / ancho, para reservar el espacio antes de dibujar
const Lienzos = ref([]);
let Documento_Pdf = null;
let Observador = null;
const Paginas_Dibujadas = new Set();

function Dibujar_Marca_Agua(Contexto, Ancho, Alto) {
    if (!Propiedades.Texto_Marca_Agua) return;
    const Tamano_Letra = Math.max(14, Math.round(Ancho * 0.03));
    Contexto.save();
    Contexto.globalAlpha = 0.15;
    Contexto.fillStyle = '#0A1628';
    Contexto.font = `700 ${Tamano_Letra}px Manrope, sans-serif`;
    Contexto.translate(Ancho / 2, Alto / 2);
    Contexto.rotate(-Math.PI / 6);
    const Ancho_Texto = Contexto.measureText(Propiedades.Texto_Marca_Agua).width + Tamano_Letra * 4;
    const Diagonal = Math.hypot(Ancho, Alto);
    // Repite el texto en una grilla que cubre toda la página girada
    for (let Y = -Diagonal / 2; Y < Diagonal / 2; Y += Tamano_Letra * 6) {
        const Desfase = (Math.round(Y / (Tamano_Letra * 6)) % 2) * (Ancho_Texto / 2);
        for (let X = -Diagonal / 2 - Desfase; X < Diagonal / 2; X += Ancho_Texto) {
            Contexto.fillText(Propiedades.Texto_Marca_Agua, X, Y);
        }
    }
    Contexto.restore();
}

async function Dibujar_Pagina(Numero_Pagina) {
    if (Paginas_Dibujadas.has(Numero_Pagina)) return;
    Paginas_Dibujadas.add(Numero_Pagina);
    const Lienzo = Lienzos.value[Numero_Pagina - 1];
    const Pagina = await Documento_Pdf.getPage(Numero_Pagina);
    const Densidad = Math.min(window.devicePixelRatio || 1, 2);
    const Escala = (Lienzo.parentElement.clientWidth / Pagina.getViewport({ scale: 1 }).width) * Densidad;
    const Vista = Pagina.getViewport({ scale: Escala });
    Lienzo.width = Math.floor(Vista.width);
    Lienzo.height = Math.floor(Vista.height);
    const Contexto = Lienzo.getContext('2d');
    await Pagina.render({ canvasContext: Contexto, viewport: Vista }).promise;
    Dibujar_Marca_Agua(Contexto, Lienzo.width, Lienzo.height);
    if (Numero_Pagina === Cantidad_Paginas.value) Emitir('Lectura_Completa');
}

onMounted(async () => {
    try {
        // PDF.js se carga solo en esta pantalla (es pesado)
        const Pdfjs = await import('pdfjs-dist');
        Pdfjs.GlobalWorkerOptions.workerSrc = Url_Trabajador_Pdf;
        Documento_Pdf = await Pdfjs.getDocument({ url: Propiedades.Url_Documento, isEvalSupported: false }).promise;
        const Primera_Pagina = (await Documento_Pdf.getPage(1)).getViewport({ scale: 1 });
        Proporcion_Pagina.value = Primera_Pagina.height / Primera_Pagina.width;
        Cantidad_Paginas.value = Documento_Pdf.numPages;
        Estado.value = 'listo';
        await nextTick();
        // Dibuja cada página cuando está por aparecer en pantalla
        Observador = new IntersectionObserver((Entradas) => {
            Entradas.filter((Entrada) => Entrada.isIntersecting)
                .forEach((Entrada) => Dibujar_Pagina(Number(Entrada.target.dataset.pagina)));
        }, { rootMargin: '400px 0px' });
        Lienzos.value.forEach((Lienzo) => Observador.observe(Lienzo));
    } catch {
        Estado.value = 'error';
    }
});

onBeforeUnmount(() => {
    Observador?.disconnect();
    Documento_Pdf?.destroy();
});
</script>

<template>
    <div :class="$style.visor" @contextmenu.prevent @dragstart.prevent @selectstart.prevent>
        <div v-if="Estado === 'cargando'" :class="$style.message"><Componente_Icono Nombre="cargando" :Tamano="28" class="girando" />Preparando el reporte…</div>
        <div v-else-if="Estado === 'error'" :class="$style.message"><Componente_Icono Nombre="alerta" :Tamano="28" />No se pudo abrir el reporte. Intente más tarde o avise a su ejecutiva.</div>
        <div v-for="Numero in Cantidad_Paginas" :key="Numero" :class="$style.page" :style="{ aspectRatio: `1 / ${Proporcion_Pagina}` }">
            <canvas :ref="(Lienzo) => { if (Lienzo) Lienzos[Numero - 1] = Lienzo; }" :data-pagina="Numero" :class="$style.canvas" />
            <span :class="$style.number">{{ Numero }} / {{ Cantidad_Paginas }}</span>
        </div>
    </div>
    <p :class="$style.printNotice">Este reporte no se puede imprimir.</p>
</template>

<style module>
.visor { display: flex; flex-direction: column; align-items: center; gap: 18px; user-select: none; -webkit-user-select: none; -webkit-touch-callout: none; }
.message { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 60px 20px; color: var(--muted); text-align: center; }
.page { position: relative; width: 100%; max-width: 900px; background: #fff; border-radius: 8px; box-shadow: 0 8px 28px rgba(10, 22, 40, 0.12); overflow: hidden; }
.canvas { display: block; width: 100%; height: 100%; pointer-events: none; }
.number { position: absolute; right: 10px; bottom: 8px; font-size: 11px; font-weight: 700; color: var(--muted); background: rgba(255, 255, 255, 0.85); padding: 2px 8px; border-radius: 999px; }
.printNotice { display: none; }
@media print {
  .visor { display: none !important; }
  .printNotice { display: block; }
}
</style>
