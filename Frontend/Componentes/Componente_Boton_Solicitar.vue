<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Componente_Icono from './Componente_Icono.vue';

/*
 * "Solicitar" un producto del ecosistema. Solo el administrador delegado lo pide (llega por correo a Blockfinity);
 * si ya está contratado o pedido, se muestra el estado en lugar del botón.
 */
const Propiedades = defineProps({
    Producto: { type: Object, required: true },
    Puede_Solicitar: { type: Boolean, default: false },
    Claro: { type: Boolean, default: false },   // sobre fondos oscuros
});
const Dialogo = ref(null);
const Formulario = useForm({ comentario: '' });

const Abrir_Dialogo = () => Dialogo.value?.showModal();
const Cerrar_Dialogo = () => Dialogo.value?.close();
const Enviar_Solicitud = () => Formulario.post(`/ecosistema/${Propiedades.Producto.Id}/solicitar`, {
    preserveScroll: true,
    onSuccess: () => { Formulario.reset(); Cerrar_Dialogo(); },
});
</script>

<template>
    <span v-if="Producto.Esta_Contratado" class="badge badge-ok" :class="$style.state"><Componente_Icono Nombre="visto" :Tamano="14" />Contratado</span>
    <span v-else-if="Producto.Esta_Solicitado" class="badge badge-blue" :class="$style.state"><Componente_Icono Nombre="reloj" :Tamano="14" />Solicitud enviada</span>
    <button v-else-if="Puede_Solicitar" type="button" class="btn btn-primary btn-sm" :class="Claro && $style.light" @click.prevent.stop="Abrir_Dialogo">
        <Componente_Icono Nombre="correo" :Tamano="15" />Solicitar
    </button>
    <span v-else :class="[$style.hint, Claro && $style.hintLight]" title="El administrador delegado de su institución puede solicitarlo">
        <Componente_Icono Nombre="candado" :Tamano="13" />Lo solicita su administrador
    </span>

    <dialog v-if="Puede_Solicitar && !Producto.Esta_Contratado" ref="Dialogo" :class="$style.dialog" @click.self="Cerrar_Dialogo">
        <form class="stack" :class="$style.form" @submit.prevent="Enviar_Solicitud">
            <div class="row"><span class="burbuja"><Componente_Icono Nombre="correo" /></span><h3>Solicitar {{ Producto.Titulo }}</h3></div>
            <p class="muted">Avisaremos a su ejecutivo de cuenta para habilitarlo. Si quiere, cuéntenos qué necesita.</p>
            <div class="field">
                <label :for="`comentario-${Producto.Id}`">Comentario (opcional)</label>
                <textarea :id="`comentario-${Producto.Id}`" v-model="Formulario.comentario" class="textarea" maxlength="1000"
                          placeholder="Ej.: para 20 personas del área de Cumplimiento" />
                <span v-if="Formulario.errors.comentario" class="error">{{ Formulario.errors.comentario }}</span>
            </div>
            <div class="row" :class="$style.actions">
                <button type="button" class="btn btn-secondary" @click="Cerrar_Dialogo">Cancelar</button>
                <button type="submit" class="btn btn-primary" :disabled="Formulario.processing">
                    <Componente_Icono :Nombre="Formulario.processing ? 'cargando' : 'correo'" :Tamano="16" :class="Formulario.processing && 'girando'" />Enviar solicitud
                </button>
            </div>
        </form>
    </dialog>
</template>

<style module>
.state { gap: 4px; }
.light { background: #fff; color: var(--navy-950); box-shadow: none; }
.light:hover:not(:disabled) { color: var(--navy-950); }
.hint { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; color: var(--muted); }
.hintLight { color: var(--side-text); }
.dialog {
  width: min(480px, calc(100vw - 32px)); border: 0; border-radius: var(--radius-lg); padding: 0; color: var(--ink);
  background: var(--surface); box-shadow: 0 30px 60px rgba(10, 22, 40, 0.35); text-align: left;
}
.dialog[open] { animation: aparecer-escala 0.25s var(--ease) both; }
.dialog::backdrop { background: rgba(10, 22, 40, 0.45); backdrop-filter: blur(2px); }
.form { padding: 24px; }  /* el padding va en el form: así un clic en el fondo oscuro (y no en la caja) cierra */
.actions { justify-content: flex-end; }
</style>
