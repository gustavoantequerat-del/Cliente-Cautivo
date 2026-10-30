<script setup>
/* Campo de contraseña con botón para mostrarla u ocultarla */
import { ref } from 'vue';
import Componente_Icono from './Componente_Icono.vue';

const Contrasena = defineModel({ type: String, default: '' });
defineProps({
    Id: { type: String, required: true },
    Autocompletar: { type: String, default: 'current-password' },
    Tiene_Error: { type: Boolean, default: false },
});
const Esta_Visible = ref(false);
</script>

<template>
    <div :class="$style.wrap">
        <input :id="Id" v-model="Contrasena" :type="Esta_Visible ? 'text' : 'password'" class="input" :class="$style.input"
               :autocomplete="Autocompletar" :aria-invalid="Tiene_Error ? 'true' : undefined" required>
        <button type="button" :class="$style.toggle" :aria-label="Esta_Visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                :aria-pressed="Esta_Visible ? 'true' : 'false'" @click="Esta_Visible = !Esta_Visible">
            <Componente_Icono :Nombre="Esta_Visible ? 'ojo_tachado' : 'ojo'" :Tamano="18" />
        </button>
    </div>
</template>

<style module>
.wrap { position: relative; }
.input { padding-right: 48px; }
.toggle {
  position: absolute; right: 6px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; border-radius: 10px;
  border: 0; background: none; color: var(--muted); cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
}
.toggle:hover { background: var(--blue-100); color: var(--navy-700); }
</style>
