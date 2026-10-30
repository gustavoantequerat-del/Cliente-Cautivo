<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Campo_Contrasena from '../../Componentes/Componente_Campo_Contrasena.vue';
import Componente_Indicador_Contrasena from '../../Componentes/Componente_Indicador_Contrasena.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

const Usuario_Actual = usePage().props.Usuario_Actual;
// current_password, password y password_confirmation son los nombres que usan las reglas de Laravel
const Formulario = useForm({ current_password: '', password: '', password_confirmation: '' });
const Contrasenas_Distintas = computed(() => Formulario.password_confirmation && Formulario.password !== Formulario.password_confirmation);
const Cambiar_Contrasena = () => Formulario.put('/perfil/contrasena', { preserveScroll: true, onSuccess: () => Formulario.reset() });
</script>

<template>
    <Head title="Mi perfil" />
    <Componente_Encabezado_Pagina Icono="usuario" Titulo="Mi perfil" />
    <div class="grid-2">
        <section class="card stack">
            <div :class="$style.identity">
                <span :class="$style.avatar">{{ Usuario_Actual.Iniciales }}</span>
                <div>
                    <h2>{{ Usuario_Actual.Nombre }}</h2>
                    <span class="muted">{{ Usuario_Actual.Area ?? '—' }}</span>
                </div>
            </div>
            <dl :class="$style.dl">
                <div><dt><Componente_Icono Nombre="correo" :Tamano="16" />Correo</dt><dd>{{ Usuario_Actual.Email }}</dd></div>
                <div v-if="Usuario_Actual.Numero_Credencial"><dt><Componente_Icono Nombre="credencial" :Tamano="16" />Credencial</dt><dd class="mono">#{{ Usuario_Actual.Numero_Credencial }}</dd></div>
            </dl>
            <p class="muted" :class="$style.note"><Componente_Icono Nombre="info" :Tamano="14" />Para cambiar sus datos, escriba al administrador de su institución.</p>
        </section>
        <form class="card stack" @submit.prevent="Cambiar_Contrasena">
            <div class="row"><span class="burbuja burbuja-sm"><Componente_Icono Nombre="candado" :Tamano="18" /></span><h2>Cambiar contraseña</h2></div>
            <div class="field">
                <label for="contrasena-actual">Contraseña actual</label>
                <Componente_Campo_Contrasena Id="contrasena-actual" v-model="Formulario.current_password" :Tiene_Error="!!Formulario.errors.current_password" />
                <span v-if="Formulario.errors.current_password" class="error">{{ Formulario.errors.current_password }}</span>
            </div>
            <div class="field">
                <label for="contrasena-nueva">Nueva contraseña</label>
                <Componente_Campo_Contrasena Id="contrasena-nueva" v-model="Formulario.password" Autocompletar="new-password" :Tiene_Error="!!Formulario.errors.password" />
                <Componente_Indicador_Contrasena :Contrasena="Formulario.password" />
                <span v-if="Formulario.errors.password" class="error">{{ Formulario.errors.password }}</span>
            </div>
            <div class="field">
                <label for="contrasena-confirmacion">Repita la nueva contraseña</label>
                <Componente_Campo_Contrasena Id="contrasena-confirmacion" v-model="Formulario.password_confirmation" Autocompletar="new-password" :Tiene_Error="!!Contrasenas_Distintas" />
                <span v-if="Contrasenas_Distintas" class="error">Las contraseñas no coinciden.</span>
            </div>
            <button type="submit" class="btn btn-primary" :disabled="Formulario.processing || Contrasenas_Distintas">Guardar</button>
        </form>
    </div>
</template>

<style module>
.identity { display: flex; align-items: center; gap: 16px; }
.avatar {
  width: 64px; height: 64px; border-radius: 20px; flex: none; display: inline-flex; align-items: center; justify-content: center;
  background: var(--gradient-primary); color: #fff; font-size: 22px; font-weight: 800; box-shadow: 0 10px 24px rgba(15, 34, 64, 0.25);
}
.dl { margin: 0; display: flex; flex-direction: column; gap: 12px; }
.dl div { display: flex; justify-content: space-between; gap: 12px; padding: 12px 14px; border-radius: 12px; background: var(--surface-2); }
.dl dt { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: var(--muted); }
.dl dd { margin: 0; font-weight: 700; }
.note { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; }
</style>
