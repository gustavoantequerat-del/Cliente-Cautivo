<script setup>
/* Acceso exclusivo del staff de Blockfinity: separado del login de clientes por seguridad */
import { Head, useForm } from '@inertiajs/vue3';
import Componente_Marca_Blockfinity from '../../Componentes/Componente_Marca_Blockfinity.vue';
import Componente_Mensaje_Flash from '../../Componentes/Componente_Mensaje_Flash.vue';
import Componente_Campo_Contrasena from '../../Componentes/Componente_Campo_Contrasena.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

const Formulario = useForm({ email: '', password: '' });
const Iniciar_Sesion = () => Formulario.post('/admin/login', { onFinish: () => Formulario.reset('password') });
</script>

<template>
    <Head title="Consola de administración" />
    <div :class="$style.page">
        <span :class="$style.grid" aria-hidden="true" />
        <Componente_Mensaje_Flash />
        <div :class="$style.card" class="animar-entrada">
            <div :class="$style.top">
                <Componente_Marca_Blockfinity Fondo_Claro />
                <span :class="$style.tag">CMS</span>
            </div>
            <div :class="$style.shield"><Componente_Icono Nombre="escudo" :Tamano="30" /></div>
            <div :class="$style.heading">
                <h1 :class="$style.h">Consola de administración</h1>
                <p class="muted">Acceso solo para el staff de Blockfinity.</p>
            </div>
            <form class="stack" novalidate @submit.prevent="Iniciar_Sesion">
                <div class="field">
                    <label for="email">Correo de staff</label>
                    <input id="email" v-model.trim="Formulario.email" class="input" type="email" inputmode="email" autocomplete="username"
                           maxlength="255" required autofocus :aria-invalid="Formulario.errors.email ? 'true' : undefined">
                    <span v-if="Formulario.errors.email" class="error">{{ Formulario.errors.email }}</span>
                </div>
                <div class="field">
                    <label for="password">Contraseña</label>
                    <Componente_Campo_Contrasena Id="password" v-model="Formulario.password" :Tiene_Error="!!Formulario.errors.password" />
                    <span v-if="Formulario.errors.password" class="error">{{ Formulario.errors.password }}</span>
                </div>
                <button class="btn btn-primary" :class="$style.full" type="submit" :disabled="Formulario.processing">
                    <Componente_Icono :Nombre="Formulario.processing ? 'cargando' : 'candado'" :Tamano="18" :class="Formulario.processing && 'girando'" />
                    {{ Formulario.processing ? 'Verificando…' : 'Entrar a la consola' }}
                </button>
            </form>
            <p :class="$style.foot"><Componente_Icono Nombre="info" :Tamano="14" />Cada ingreso queda registrado en la auditoría.</p>
        </div>
    </div>
</template>

<style module>
.page {
  position: relative; overflow: hidden; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px;
  background: radial-gradient(900px 600px at 50% -10%, #1F3F73 0%, transparent 60%), #070F1D;
}
.grid {
  position: absolute; inset: 0; pointer-events: none; opacity: 0.35;
  background-image: linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
  background-size: 44px 44px;
  mask-image: radial-gradient(circle at 50% 40%, #000 0%, transparent 70%);
}
.card {
  position: relative; width: 100%; max-width: 420px; background: var(--surface); border-radius: 24px; padding: 32px;
  display: flex; flex-direction: column; gap: 22px; box-shadow: 0 30px 80px rgba(0, 0, 0, 0.45);
}
.top { display: flex; align-items: center; justify-content: space-between; }
.tag { font-size: 11px; font-weight: 800; letter-spacing: 0.12em; padding: 4px 10px; border-radius: 8px; background: var(--navy-950); color: #fff; }
.shield {
  align-self: center; width: 68px; height: 68px; border-radius: 22px; display: flex; align-items: center; justify-content: center;
  background: var(--gradient-primary); color: #fff; box-shadow: 0 12px 28px rgba(15, 34, 64, 0.35); animation: aparecer-escala 0.6s var(--ease) 0.15s both;
}
.heading { text-align: center; display: flex; flex-direction: column; gap: 4px; }
.h { font-size: 24px; }
.full { width: 100%; min-height: 52px; font-size: 16px; }
.foot { display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 12px; color: var(--muted); }
</style>
