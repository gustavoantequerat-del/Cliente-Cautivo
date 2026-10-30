<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Plantilla_Acceso from '../../Plantillas/Plantilla_Acceso.vue';
import Componente_Campo_Contrasena from '../../Componentes/Componente_Campo_Contrasena.vue';
import Componente_Indicador_Contrasena from '../../Componentes/Componente_Indicador_Contrasena.vue';

const Propiedades = defineProps({ Token: String, Email: String });
// token, email, password y password_confirmation son los nombres que exige Laravel para restablecer
const Formulario = useForm({ token: Propiedades.Token, email: Propiedades.Email ?? '', password: '', password_confirmation: '' });
const Contrasenas_Distintas = computed(() => Formulario.password_confirmation && Formulario.password !== Formulario.password_confirmation);
</script>

<template>
    <Head title="Nueva contraseña" />
    <Plantilla_Acceso>
        <h2>Crear nueva contraseña</h2>
        <form class="stack" @submit.prevent="Formulario.post('/restablecer')">
            <div class="field">
                <label for="email">Correo</label>
                <input id="email" v-model.trim="Formulario.email" class="input" type="email" inputmode="email" maxlength="255" required>
                <span v-if="Formulario.errors.email" class="error">{{ Formulario.errors.email }}</span>
            </div>
            <div class="field">
                <label for="password">Nueva contraseña</label>
                <Componente_Campo_Contrasena Id="password" v-model="Formulario.password" Autocompletar="new-password" />
                <Componente_Indicador_Contrasena :Contrasena="Formulario.password" />
                <span v-if="Formulario.errors.password" class="error">{{ Formulario.errors.password }}</span>
            </div>
            <div class="field">
                <label for="password_confirmation">Repita la contraseña</label>
                <Componente_Campo_Contrasena Id="password_confirmation" v-model="Formulario.password_confirmation" Autocompletar="new-password" :Tiene_Error="!!Contrasenas_Distintas" />
                <span v-if="Contrasenas_Distintas" class="error">Las contraseñas no coinciden.</span>
            </div>
            <button class="btn btn-primary" type="submit" :disabled="Formulario.processing || Contrasenas_Distintas">Guardar contraseña</button>
        </form>
    </Plantilla_Acceso>
</template>
