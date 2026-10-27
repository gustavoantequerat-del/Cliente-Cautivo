<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthShell from './AuthShell.vue';

defineProps({ status: String });
const form = useForm({ email: '' });
</script>

<template>
    <Head title="Recuperar contraseña" />
    <AuthShell>
        <div class="stack">
            <h2>Recuperar contraseña</h2>
            <p class="muted">Le enviaremos un enlace para crear una nueva contraseña.</p>
        </div>
        <p v-if="status" class="badge badge-ok" :class="$style.wrapText">{{ status }}</p>
        <form class="stack" @submit.prevent="form.post('/olvide-mi-contrasena')">
            <div class="field">
                <label for="email">Correo institucional</label>
                <input id="email" v-model="form.email" class="input" type="email" required autofocus>
                <span v-if="form.errors.email" class="error">{{ form.errors.email }}</span>
            </div>
            <button class="btn btn-primary" type="submit" :disabled="form.processing">Enviar enlace</button>
            <Link href="/login">← Volver a ingresar</Link>
        </form>
    </AuthShell>
</template>

<style module>
.wrapText { white-space: normal; line-height: 1.4; padding: 10px 14px; border-radius: 10px; }
</style>
