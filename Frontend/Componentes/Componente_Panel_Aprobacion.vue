<script setup>
import { useForm } from '@inertiajs/vue3';
import Componente_Icono from './Componente_Icono.vue';

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
            <span :class="$style.kicker">Requiere su aprobación</span>
            <h2 :class="$style.title">{{ Entregable.Titulo }}</h2>
            <p :class="$style.meta">
                <template v-if="Entregable.Version">Versión {{ Entregable.Version }} · </template>publicado el {{ Entregable.Fecha }}
            </p>
            <p v-if="Entregable.Huella_Sha256" :class="$style.hash">Huella SHA-256: <span class="mono">{{ Entregable.Huella_Sha256 }}</span></p>
        </div>
        <form :class="$style.form" @submit.prevent="Aprobar_Entregable">
            <a v-if="Entregable.Tiene_Archivo" :href="`/entregables/${Entregable.Id}/descargar`" target="_blank" rel="noopener" :class="$style.ghost">
                <Componente_Icono Nombre="archivo" :Tamano="18" />Revisar documento
            </a>
            <div class="field">
                <label :for="`contrasena-${Entregable.Id}`" :class="$style.label">Confirme con su contraseña</label>
                <input :id="`contrasena-${Entregable.Id}`" v-model="Formulario.password" type="password" class="input" autocomplete="current-password" required>
                <span v-if="Formulario.errors.password" :class="$style.err">{{ Formulario.errors.password }}</span>
            </div>
            <button type="submit" :class="$style.approve" :disabled="Formulario.processing || !Formulario.password">
                <Componente_Icono Nombre="visto" :Tamano="18" />Aprobar entregable
            </button>
            <span :class="$style.note">Se registrará su nombre, fecha, IP y la huella exacta del documento aprobado.</span>
        </form>
    </section>
</template>

<style module>
.panel {
  background: var(--navy-950); color: #fff; border-radius: var(--radius-lg); padding: 24px;
  display: flex; flex-wrap: wrap; gap: 24px; justify-content: space-between;
}
.info { flex: 1 1 340px; display: flex; flex-direction: column; gap: 8px; min-width: 0; }
.kicker { font-size: 11px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: var(--side-label); }
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
  display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; border-radius: 10px;
  border: 0; background: #fff; color: var(--navy-950); font-weight: 800; font-size: 15px; cursor: pointer;
}
.approve:disabled { opacity: 0.6; cursor: not-allowed; }
.note { font-size: 12px; color: var(--side-label); }
</style>
