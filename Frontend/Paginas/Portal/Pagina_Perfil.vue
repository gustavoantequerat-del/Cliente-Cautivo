<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';

const Usuario_Actual = usePage().props.Usuario_Actual;
// current_password, password y password_confirmation son los nombres que usan las reglas de Laravel
const Formulario = useForm({ current_password: '', password: '', password_confirmation: '' });
const Cambiar_Contrasena = () => Formulario.put('/perfil/contrasena', { preserveScroll: true, onSuccess: () => Formulario.reset() });
</script>

<template>
    <Head title="Mi perfil" />
    <Componente_Encabezado_Pagina Titulo="Mi perfil" />
    <div class="grid-2">
        <section class="card stack">
            <h2>Datos</h2>
            <dl :class="$style.dl">
                <div><dt>Nombre</dt><dd>{{ Usuario_Actual.Nombre }}</dd></div>
                <div><dt>Correo</dt><dd>{{ Usuario_Actual.Email }}</dd></div>
                <div><dt>Área</dt><dd>{{ Usuario_Actual.Area ?? '—' }}</dd></div>
            </dl>
            <p class="muted">Para cambiar estos datos, contacte al administrador de su institución.</p>
        </section>
        <form class="card stack" @submit.prevent="Cambiar_Contrasena">
            <h2>Cambiar contraseña</h2>
            <div class="field">
                <label for="contrasena-actual">Contraseña actual</label>
                <input id="contrasena-actual" v-model="Formulario.current_password" type="password" class="input" autocomplete="current-password" required>
                <span v-if="Formulario.errors.current_password" class="error">{{ Formulario.errors.current_password }}</span>
            </div>
            <div class="field">
                <label for="contrasena-nueva">Nueva contraseña</label>
                <input id="contrasena-nueva" v-model="Formulario.password" type="password" class="input" autocomplete="new-password" required>
                <span class="help">Mínimo 10 caracteres, con letras y números.</span>
                <span v-if="Formulario.errors.password" class="error">{{ Formulario.errors.password }}</span>
            </div>
            <div class="field">
                <label for="contrasena-confirmacion">Repita la nueva contraseña</label>
                <input id="contrasena-confirmacion" v-model="Formulario.password_confirmation" type="password" class="input" autocomplete="new-password" required>
            </div>
            <button type="submit" class="btn btn-primary" :disabled="Formulario.processing">Guardar</button>
        </form>
    </div>
</template>

<style module>
.dl { margin: 0; display: flex; flex-direction: column; gap: 12px; }
.dl div { display: flex; flex-direction: column; }
.dl dt { font-size: 12px; color: var(--muted); }
.dl dd { margin: 0; font-weight: 700; }
</style>
