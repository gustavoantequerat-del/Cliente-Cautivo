<script setup>
import { useForm } from '@inertiajs/vue3';
import Componente_Icono from './Componente_Icono.vue';
import Componente_Campo_Contrasena from './Componente_Campo_Contrasena.vue';

const Propiedades = defineProps({ Entregable: { type: Object, required: true } });
const Formulario = useForm({ password: '' });
const Aprobar_Entregable = () => Formulario.post(`/entregables/${Propiedades.Entregable.Id}/aprobar`, {
    preserveScroll: true,
    onSuccess: () => Formulario.reset(),
});
</script>

<template>
    <section :class="$style.panel" aria-label="Aprobación pendiente">
        <div :class="$style.info">
            <span :class="$style.kicker"><span :class="$style.pulse" />Requiere su aprobación</span>
            <h2 :class="$style.title">{{ Entregable.Titulo }}</h2>
            <p :class="$style.meta">
                <template v-if="Entregable.Version">Versión {{ Entregable.Version }} · </template>publicado el {{ Entregable.Fecha }}
            </p>
            <p v-if="Entregable.Codigo_Verificacion" :class="$style.hash" title="Código único del archivo: si el documento cambia, el código cambia">Código de verificación: <span class="mono">{{ Entregable.Codigo_Verificacion.slice(0, 12) }}…</span></p>
        </div>
        <form :class="$style.form" @submit.prevent="Aprobar_Entregable">
            <a v-if="Entregable.Tiene_Archivo" :href="`/entregables/${Entregable.Id}/descargar`" target="_blank" rel="noopener" :class="$style.ghost">
                <Componente_Icono Nombre="archivo" :Tamano="18" />Revisar documento
            </a>
            <div class="field">
                <label :for="`contrasena-${Entregable.Id}`" :class="$style.label">Confirme con su contraseña</label>
                <Componente_Campo_Contrasena :Id="`contrasena-${Entregable.Id}`" v-model="Formulario.password" :Tiene_Error="!!Formulario.errors.password" />
                <span v-if="Formulario.errors.password" :class="$style.err">{{ Formulario.errors.password }}</span>
            </div>
            <button type="submit" :class="$style.approve" :disabled="Formulario.processing || !Formulario.password">
                <Componente_Icono Nombre="visto" :Tamano="18" />Aprobar entregable
            </button>
            <span :class="$style.note"><Componente_Icono Nombre="codigo" :Tamano="14" />Queda registrado qué versión exacta aprobó.</span>
        </form>
    </section>
</template>

<style module>
.panel {
  animation: aparecer-escala 0.5s var(--ease) both; box-shadow: 0 18px 40px rgba(15, 34, 64, 0.25);
  background: var(--gradient-hero); color: #fff; border-radius: var(--radius-lg); padding: 24px;
  display: flex; flex-wrap: wrap; gap: 24px; justify-content: space-between;
}
.info { flex: 1 1 340px; display: flex; flex-direction: column; gap: 8px; min-width: 0; }
.kicker { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: var(--side-label); }
.title { color: #fff; font-size: 22px; }
.meta { color: var(--side-text); font-size: 14px; }
.hash { color: var(--side-label); font-size: 12px; word-break: break-all; }
.form { flex: 0 1 340px; display: flex; flex-direction: column; gap: 12px; }
.label { color: var(--side-text) !important; }
.err { color: #FFB4AB; font-size: 13px; font-weight: 600; }
.ghost {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; border-radius: 10px;
  border: 1px solid var(--navy-500); color: #fff; text-decoration: none; font-weight: 700; font-size: 14px;
}
.ghost:hover { background: var(--navy-800); color: #fff; }
.approve {
  transition: transform 0.15s var(--ease), box-shadow 0.2s;
  display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; border-radius: 10px;
  border: 0; background: #fff; color: var(--navy-950); font-weight: 800; font-size: 15px; cursor: pointer;
}
.approve:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(0, 0, 0, 0.3); }
.approve:disabled { opacity: 0.6; cursor: not-allowed; }
.note { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: var(--side-label); }
.pulse { width: 8px; height: 8px; border-radius: 50%; background: #FF6B5E; animation: latido 1.8s infinite; }
</style>
