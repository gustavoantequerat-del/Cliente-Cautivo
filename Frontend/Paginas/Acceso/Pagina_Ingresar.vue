<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Plantilla_Acceso from '../../Plantillas/Plantilla_Acceso.vue';
import Componente_Mensaje_Flash from '../../Componentes/Componente_Mensaje_Flash.vue';

// Los nombres email y password son los que exige el login de Laravel
const Formulario = useForm({ email: '', password: '', recordarme: false });
const Iniciar_Sesion = () => Formulario.post('/login', { onFinish: () => Formulario.reset('password') });
</script>

<template>
    <Head title="Ingresar" />
    <Plantilla_Acceso>
        <div class="stack">
            <h2 :class="$style.h">Ingresar al portal</h2>
            <p class="muted">Use su correo institucional y la contraseña que le envió su administrador.</p>
        </div>
        <Componente_Mensaje_Flash />
        <form class="stack" novalidate @submit.prevent="Iniciar_Sesion">
            <div class="field">
                <label for="email">Correo institucional</label>
                <input id="email" v-model="Formulario.email" class="input" type="email" autocomplete="username" required autofocus>
                <span v-if="Formulario.errors.email" class="error">{{ Formulario.errors.email }}</span>
            </div>
            <div class="field">
                <label for="password">Contraseña</label>
                <input id="password" v-model="Formulario.password" class="input" type="password" autocomplete="current-password" required>
                <span v-if="Formulario.errors.password" class="error">{{ Formulario.errors.password }}</span>
            </div>
            <div class="row between">
                <label class="checkbox"><input v-model="Formulario.recordarme" type="checkbox">Recordarme en este equipo</label>
                <Link href="/olvide-mi-contrasena" :class="$style.small">¿Olvidó su contraseña?</Link>
            </div>
            <button class="btn btn-primary" :class="$style.full" type="submit" :disabled="Formulario.processing">
                {{ Formulario.processing ? 'Ingresando…' : 'Ingresar' }}
            </button>
        </form>
        <p :class="$style.foot">¿Aún no tiene credencial? Solicítela al administrador de su institución o a su ejecutiva de cuenta.</p>
    </Plantilla_Acceso>
</template>

<style module>
.h { font-size: 28px; }
.full { width: 100%; min-height: 52px; font-size: 16px; }
.small { font-size: 13px; font-weight: 700; }
.foot { font-size: 13px; color: var(--muted); border-top: 1px solid var(--line); padding-top: 18px; }
</style>
