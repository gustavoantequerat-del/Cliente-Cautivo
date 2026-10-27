<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthShell from './AuthShell.vue';

const props = defineProps({ token: String, email: String });
const form = useForm({ token: props.token, email: props.email ?? '', password: '', password_confirmation: '' });
</script>

<template>
    <Head title="Nueva contraseña" />
    <AuthShell>
        <h2>Crear nueva contraseña</h2>
        <form class="stack" @submit.prevent="form.post('/restablecer')">
            <div class="field">
                <label for="email">Correo</label>
                <input id="email" v-model="form.email" class="input" type="email" required>
                <span v-if="form.errors.email" class="error">{{ form.errors.email }}</span>
            </div>
            <div class="field">
                <label for="password">Nueva contraseña</label>
                <input id="password" v-model="form.password" class="input" type="password" autocomplete="new-password" required>
                <span class="help">Mínimo 10 caracteres, con letras y números.</span>
                <span v-if="form.errors.password" class="error">{{ form.errors.password }}</span>
            </div>
            <div class="field">
                <label for="password_confirmation">Repita la contraseña</label>
                <input id="password_confirmation" v-model="form.password_confirmation" class="input" type="password" autocomplete="new-password" required>
            </div>
            <button class="btn btn-primary" type="submit" :disabled="form.processing">Guardar contraseña</button>
        </form>
    </AuthShell>
</template>
