<script setup>
/*
 * Ilustración SVG animada: una red de bloques conectados con pulsos de datos que viajan por las líneas.
 * Es decorativa (aria-hidden) y se detiene si el sistema pide menos movimiento (prefers-reduced-motion).
 */
defineProps({ Tamano: { type: Number, default: 360 } });

const Nodos = [
    { X: 60, Y: 70, R: 7 }, { X: 170, Y: 40, R: 10 }, { X: 290, Y: 85, R: 8 },
    { X: 110, Y: 180, R: 12 }, { X: 235, Y: 190, R: 9 }, { X: 330, Y: 230, R: 6 },
    { X: 55, Y: 290, R: 8 }, { X: 180, Y: 300, R: 10 }, { X: 300, Y: 330, R: 7 },
];
const Conexiones = [[0, 1], [1, 2], [0, 3], [1, 3], [1, 4], [2, 4], [2, 5], [3, 4], [4, 5], [3, 6], [3, 7], [4, 7], [5, 8], [7, 8], [6, 7]];
const Camino = ([Desde, Hasta]) => `M${Nodos[Desde].X} ${Nodos[Desde].Y} L${Nodos[Hasta].X} ${Nodos[Hasta].Y}`;
</script>

<template>
    <svg :width="Tamano" :height="Tamano" viewBox="0 0 380 380" fill="none" aria-hidden="true" :class="$style.red">
        <defs>
            <linearGradient id="red-linea" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="currentColor" stop-opacity="0.55" />
                <stop offset="1" stop-color="currentColor" stop-opacity="0.15" />
            </linearGradient>
            <radialGradient id="red-nodo">
                <stop offset="0" stop-color="#FFFFFF" />
                <stop offset="1" stop-color="currentColor" />
            </radialGradient>
        </defs>
        <g :class="$style.lineas">
            <path v-for="(Conexion, Posicion) in Conexiones" :key="`l${Posicion}`" :d="Camino(Conexion)" stroke="url(#red-linea)" stroke-width="1.4" />
        </g>
        <!-- Pulsos de datos: cada uno recorre una conexión con su propio ritmo -->
        <circle v-for="(Conexion, Posicion) in Conexiones.filter((_, Indice) => Indice % 2 === 0)" :key="`p${Posicion}`" r="3" fill="#FFFFFF" :class="$style.pulso">
            <animateMotion :dur="`${2.6 + (Posicion % 4) * 0.7}s`" :begin="`${Posicion * 0.45}s`" repeatCount="indefinite" :path="Camino(Conexion)" />
        </circle>
        <g v-for="(Nodo, Posicion) in Nodos" :key="`n${Posicion}`" :class="$style.nodo" :style="{ animationDelay: `${Posicion * 0.35}s`, transformOrigin: `${Nodo.X}px ${Nodo.Y}px` }">
            <circle :cx="Nodo.X" :cy="Nodo.Y" :r="Nodo.R * 2.2" fill="currentColor" opacity="0.08" />
            <circle :cx="Nodo.X" :cy="Nodo.Y" :r="Nodo.R" fill="url(#red-nodo)" />
        </g>
        <!-- Bloque (cubo isométrico) flotando en el centro -->
        <g :class="$style.bloque">
            <path d="M190 120 L232 144 L190 168 L148 144 Z" fill="#FFFFFF" fill-opacity="0.9" />
            <path d="M148 144 L190 168 L190 214 L148 190 Z" fill="currentColor" fill-opacity="0.75" />
            <path d="M232 144 L190 168 L190 214 L232 190 Z" fill="currentColor" fill-opacity="0.45" />
        </g>
    </svg>
</template>

<style module>
.red { display: block; max-width: 100%; height: auto; color: #8FC7F0; overflow: visible; }
.lineas path { stroke-dasharray: 6 8; animation: fluir 6s linear infinite; }
.nodo { animation: latir 3.2s ease-in-out infinite; }
.bloque { animation: flotar-bloque 6s ease-in-out infinite; transform-origin: 190px 167px; }
.pulso { filter: drop-shadow(0 0 4px #fff); }
@keyframes fluir { to { stroke-dashoffset: -140; } }
@keyframes latir { 0%, 100% { scale: 1; opacity: 0.85; } 50% { scale: 1.18; opacity: 1; } }
@keyframes flotar-bloque { 0%, 100% { translate: 0 0; } 50% { translate: 0 -10px; } }
@media (prefers-reduced-motion: reduce) {
  .lineas path, .nodo, .bloque { animation: none; }
  .pulso { display: none; }
}
</style>
