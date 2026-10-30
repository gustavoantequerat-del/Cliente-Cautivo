<script setup>
/* Muestra en vivo si la contraseña cumple las reglas del Backend: 10+ caracteres, letras y números */
import { computed } from 'vue';
import Componente_Icono from './Componente_Icono.vue';

const Propiedades = defineProps({ Contrasena: { type: String, default: '' } });
const Reglas = computed(() => [
    { Texto: '10+ caracteres', Cumple: Propiedades.Contrasena.length >= 10 },
    { Texto: 'Letras', Cumple: /\p{L}/u.test(Propiedades.Contrasena) },
    { Texto: 'Números', Cumple: /\d/.test(Propiedades.Contrasena) },
]);
</script>

<template>
    <ul :class="$style.rules">
        <li v-for="Regla in Reglas" :key="Regla.Texto" :class="Regla.Cumple && $style.ok">
            <Componente_Icono :Nombre="Regla.Cumple ? 'visto' : 'cerrar'" :Tamano="12" :Grosor="2.6" />{{ Regla.Texto }}
        </li>
    </ul>
</template>

<style module>
.rules { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 6px; }
.rules li {
  display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; padding: 3px 9px; border-radius: 999px;
  background: var(--surface-2); color: var(--subtle); border: 1px solid var(--line); transition: background 0.25s, color 0.25s, border-color 0.25s;
}
.rules li.ok { background: var(--ok-bg); color: var(--ok-fg); border-color: transparent; }
</style>
