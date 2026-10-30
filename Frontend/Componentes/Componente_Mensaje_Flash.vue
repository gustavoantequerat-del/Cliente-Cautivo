<script setup>
/* Aviso flotante: aparece arriba a la derecha y se oculta solo a los 5 segundos */
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Componente_Icono from './Componente_Icono.vue';

const Pagina = usePage();
const Esta_Visible = ref(false);
const Mensajes = computed(() => Pagina.props.Mensajes_Flash ?? {});
const Es_Error = computed(() => !!Mensajes.value.Error);
let Temporizador = null;

function Cerrar_Aviso() {
    Esta_Visible.value = false;
    clearTimeout(Temporizador);
}
watch(() => [Mensajes.value.Exito, Mensajes.value.Error], ([Exito, Error]) => {
    Esta_Visible.value = !!(Exito || Error);
    clearTimeout(Temporizador);
    if (Esta_Visible.value) Temporizador = setTimeout(Cerrar_Aviso, 5000);
}, { immediate: true });
</script>

<template>
    <Transition :enter-from-class="$style.oculto" :leave-to-class="$style.oculto">
        <div v-if="Esta_Visible" :class="[$style.toast, Es_Error ? $style.error : $style.success]" role="status" aria-live="polite">
            <span :class="$style.icon"><Componente_Icono :Nombre="Es_Error ? 'alerta' : 'visto'" :Tamano="18" :Grosor="2.2" /></span>
            <span :class="$style.text">{{ Mensajes.Error || Mensajes.Exito }}</span>
            <button type="button" :class="$style.close" aria-label="Cerrar mensaje" @click="Cerrar_Aviso"><Componente_Icono Nombre="cerrar" :Tamano="16" /></button>
            <span :class="$style.timer" />
        </div>
    </Transition>
</template>

<style module>
.toast {
  position: fixed; top: 20px; right: 20px; z-index: 100; width: min(420px, calc(100vw - 32px));
  display: flex; align-items: center; gap: 12px; padding: 14px 14px 16px; border-radius: 16px; overflow: hidden;
  background: var(--surface); box-shadow: 0 18px 40px rgba(10, 22, 40, 0.18); border: 1px solid var(--line);
  font-size: 14px; font-weight: 600; color: var(--ink);
  transition: opacity 0.3s var(--ease), transform 0.3s var(--ease);
}
.oculto { opacity: 0; transform: translateX(24px); }
.icon { width: 34px; height: 34px; flex: none; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; }
.success .icon { background: var(--ok-bg); color: var(--ok-fg); }
.error .icon { background: var(--danger-bg); color: var(--danger-fg); }
.text { flex: 1; line-height: 1.4; }
.close { background: none; border: 0; cursor: pointer; color: var(--muted); width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; }
.close:hover { background: var(--surface-2); color: var(--ink); }
.timer { position: absolute; left: 0; bottom: 0; height: 3px; width: 100%; transform-origin: left; animation: consumir 5s linear both; }
.success .timer { background: #3E9B66; }
.error .timer { background: #D0574B; }
@keyframes consumir { from { transform: scaleX(1); } to { transform: scaleX(0); } }
</style>
