<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Plantilla_Acceso from '../../Plantillas/Plantilla_Acceso.vue';
import Componente_Mensaje_Flash from '../../Componentes/Componente_Mensaje_Flash.vue';
import Componente_Campo_Contrasena from '../../Componentes/Componente_Campo_Contrasena.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

// Los nombres email y password son los que exige el login de Laravel
const Formulario = useForm({ email: '', password: '', recordarme: false });
const Iniciar_Sesion = () => Formulario.post('/login', { onFinish: () => Formulario.reset('password') });
</script>

<template>
    <Head title="Ingresar" />
    <Plantilla_Acceso>
        <div class="stack" :class="$style.tight">
            <h2 :class="$style.h">¡Hola de nuevo! 👋</h2>
            <p class="muted">Ingrese con su correo institucional.</p>
        </div>
        <Componente_Mensaje_Flash />
        <form class="stack" novalidate @submit.prevent="Iniciar_Sesion">
            <div class="field">
                <label for="email">Correo</label>
                <input id="email" v-model.trim="Formulario.email" class="input" type="email" inputmode="email" autocomplete="username"
                       placeholder="nombre@institucion.com" maxlength="255" required autofocus :aria-invalid="Formulario.errors.email ? 'true' : undefined">
                <span v-if="Formulario.errors.email" class="error">{{ Formulario.errors.email }}</span>
            </div>
            <div class="field">
                <label for="password">Contraseña</label>
                <Componente_Campo_Contrasena Id="password" v-model="Formulario.password" :Tiene_Error="!!Formulario.errors.password" />
                <span v-if="Formulario.errors.password" class="error">{{ Formulario.errors.password }}</span>
            </div>
            <div class="row between">
                <label class="checkbox"><input v-model="Formulario.recordarme" type="checkbox">Recordarme</label>
                <Link href="/olvide-mi-contrasena" :class="$style.small">¿Olvidó su contraseña?</Link>
            </div>
            <button class="btn btn-primary" :class="$style.full" type="submit" :disabled="Formulario.processing">
                <Componente_Icono v-if="Formulario.processing" Nombre="cargando" :Tamano="18" class="girando" />
                {{ Formulario.processing ? 'Ingresando…' : 'Ingresar' }}
                <Componente_Icono v-if="!Formulario.processing" Nombre="flecha" :Tamano="18" />
            </button>
        </form>
        <p :class="$style.foot"><Componente_Icono Nombre="info" :Tamano="16" />¿Sin credencial? Pídala a su ejecutiva de cuenta.</p>
    </Plantilla_Acceso>
</template>

<style module>
.tight { gap: 6px; }
.h { font-size: 30px; }
.full { width: 100%; min-height: 52px; font-size: 16px; }
.small { font-size: 13px; font-weight: 700; }
.foot { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--muted); border-top: 1px solid var(--line); padding-top: 18px; }
</style>
