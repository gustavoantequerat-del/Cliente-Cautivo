<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Plantilla_Acceso from '../../Plantillas/Plantilla_Acceso.vue';

const Propiedades = defineProps({ Token: String, Email: String });
// token, email, password y password_confirmation son los nombres que exige Laravel para restablecer
const Formulario = useForm({ token: Propiedades.Token, email: Propiedades.Email ?? '', password: '', password_confirmation: '' });
</script>

<template>
    <Head title="Nueva contraseña" />
    <Plantilla_Acceso>
        <h2>Crear nueva contraseña</h2>
        <form class="stack" @submit.prevent="Formulario.post('/restablecer')">
            <div class="field">
                <label for="email">Correo</label>
                <input id="email" v-model="Formulario.email" class="input" type="email" required>
                <span v-if="Formulario.errors.email" class="error">{{ Formulario.errors.email }}</span>
            </div>
            <div class="field">
                <label for="password">Nueva contraseña</label>
                <input id="password" v-model="Formulario.password" class="input" type="password" autocomplete="new-password" required>
                <span class="help">Mínimo 10 caracteres, con letras y números.</span>
                <span v-if="Formulario.errors.password" class="error">{{ Formulario.errors.password }}</span>
            </div>
            <div class="field">
                <label for="password_confirmation">Repita la contraseña</label>
                <input id="password_confirmation" v-model="Formulario.password_confirmation" class="input" type="password" autocomplete="new-password" required>
            </div>
            <button class="btn btn-primary" type="submit" :disabled="Formulario.processing">Guardar contraseña</button>
        </form>
    </Plantilla_Acceso>
</template>
