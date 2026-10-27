<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Plantilla_Acceso from '../../Plantillas/Plantilla_Acceso.vue';
import Componente_Mensaje_Flash from '../../Componentes/Componente_Mensaje_Flash.vue';

const Formulario = useForm({ email: '' });
</script>

<template>
    <Head title="Recuperar contraseña" />
    <Plantilla_Acceso>
        <div class="stack">
            <h2>Recuperar contraseña</h2>
            <p class="muted">Le enviaremos un enlace para crear una nueva contraseña.</p>
        </div>
        <Componente_Mensaje_Flash />
        <form class="stack" @submit.prevent="Formulario.post('/olvide-mi-contrasena')">
            <div class="field">
                <label for="email">Correo institucional</label>
                <input id="email" v-model="Formulario.email" class="input" type="email" required autofocus>
                <span v-if="Formulario.errors.email" class="error">{{ Formulario.errors.email }}</span>
            </div>
            <button class="btn btn-primary" type="submit" :disabled="Formulario.processing">Enviar enlace</button>
            <Link href="/login">← Volver a ingresar</Link>
        </form>
    </Plantilla_Acceso>
</template>
