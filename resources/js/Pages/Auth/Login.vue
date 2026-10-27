<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthShell from './AuthShell.vue';
import FlashMessage from '../../Components/FlashMessage.vue';

defineProps({ status: String });
const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post('/login', { onFinish: () => form.reset('password') });
</script>

<template>
    <Head title="Ingresar" />
    <AuthShell>
        <div class="stack">
            <h2 :class="$style.h">Ingresar al portal</h2>
            <p class="muted">Use su correo institucional y la contraseña que le envió su administrador.</p>
        </div>
        <FlashMessage />
        <p v-if="status" class="badge badge-ok">{{ status }}</p>
        <form class="stack" novalidate @submit.prevent="submit">
            <div class="field">
                <label for="email">Correo institucional</label>
                <input id="email" v-model="form.email" class="input" type="email" autocomplete="username" required autofocus>
                <span v-if="form.errors.email" class="error">{{ form.errors.email }}</span>
            </div>
            <div class="field">
                <label for="password">Contraseña</label>
                <input id="password" v-model="form.password" class="input" type="password" autocomplete="current-password" required>
                <span v-if="form.errors.password" class="error">{{ form.errors.password }}</span>
            </div>
            <div class="row between">
                <label class="checkbox"><input v-model="form.remember" type="checkbox">Recordarme en este equipo</label>
                <Link href="/olvide-mi-contrasena" :class="$style.small">¿Olvidó su contraseña?</Link>
            </div>
            <button class="btn btn-primary" :class="$style.full" type="submit" :disabled="form.processing">
                {{ form.processing ? 'Ingresando…' : 'Ingresar' }}
            </button>
        </form>
        <p :class="$style.foot">¿Aún no tiene credencial? Solicítela al administrador de su institución o a su ejecutiva de cuenta.</p>
    </AuthShell>
</template>

<style module>
.h { font-size: 28px; }
.full { width: 100%; min-height: 52px; font-size: 16px; }
.small { font-size: 13px; font-weight: 700; }
.foot { font-size: 13px; color: var(--muted); border-top: 1px solid var(--line); padding-top: 18px; }
</style>
