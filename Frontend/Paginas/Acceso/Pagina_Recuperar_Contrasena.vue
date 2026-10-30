<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Plantilla_Acceso from '../../Plantillas/Plantilla_Acceso.vue';
import Componente_Mensaje_Flash from '../../Componentes/Componente_Mensaje_Flash.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

const Formulario = useForm({ email: '' });
</script>

<template>
    <Head title="Recuperar contraseña" />
    <Plantilla_Acceso>
        <span class="burbuja"><Componente_Icono Nombre="correo" :Tamano="22" /></span>
        <div class="stack" :class="$style.tight">
            <h2 :class="$style.h">Recuperar contraseña</h2>
            <p class="muted">Le enviaremos un enlace a su correo.</p>
        </div>
        <Componente_Mensaje_Flash />
        <form class="stack" @submit.prevent="Formulario.post('/olvide-mi-contrasena')">
            <div class="field">
                <label for="email">Correo</label>
                <input id="email" v-model.trim="Formulario.email" class="input" type="email" inputmode="email" maxlength="255" required autofocus>
                <span v-if="Formulario.errors.email" class="error">{{ Formulario.errors.email }}</span>
            </div>
            <button class="btn btn-primary" type="submit" :disabled="Formulario.processing">Enviar enlace</button>
            <Link href="/login" class="link-arrow">← Volver</Link>
        </form>
    </Plantilla_Acceso>
</template>

<style module>
.tight { gap: 6px; }
.h { font-size: 26px; }
</style>
