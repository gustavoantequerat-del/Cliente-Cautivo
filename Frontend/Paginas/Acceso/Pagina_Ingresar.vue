<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Plantilla_Acceso from '../../Plantillas/Plantilla_Acceso.vue';
import Componente_Mensaje_Flash from '../../Componentes/Componente_Mensaje_Flash.vue';
import Componente_Campo_Contrasena from '../../Componentes/Componente_Campo_Contrasena.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

// Los nombres email y password son los que exige el login de Laravel
const Formulario = useForm({ email: '', password: '', recordarme: false, institucion_id: null });
// Si el correo tiene cuenta en varias instituciones, el Backend pide elegir una (la contraseña se conserva para reenviar)
const Pagina = usePage();
const Instituciones_Para_Elegir = computed(() => Pagina.props.Mensajes_Flash?.Instituciones_Para_Elegir ?? null);
const Iniciar_Sesion = () => Formulario.post('/login', {
    preserveState: true,
    onSuccess: () => { if (!Instituciones_Para_Elegir.value) Formulario.reset('password'); },
    onError: () => Formulario.reset('password'),
});
const Elegir_Institucion = (Id_Institucion) => { Formulario.institucion_id = Id_Institucion; Iniciar_Sesion(); };
</script>

<template>
    <Head title="Ingresar" />
    <Plantilla_Acceso>
        <div class="stack" :class="$style.tight">
            <h2 :class="$style.h">Hola de nuevo</h2>
            <p class="muted">Ingrese con su correo institucional.</p>
        </div>
        <Componente_Mensaje_Flash />
        <div v-if="Instituciones_Para_Elegir" class="stack" :class="$style.choose" role="group" aria-label="Elija su institución">
            <strong>¿A qué institución desea ingresar?</strong>
            <span class="muted" :class="$style.small">Tiene una cuenta en cada una; sus datos no se comparten entre ellas.</span>
            <button v-for="Opcion in Instituciones_Para_Elegir" :key="Opcion.Id" type="button" :class="$style.option" :disabled="Formulario.processing" @click="Elegir_Institucion(Opcion.Id)">
                <span class="burbuja burbuja-sm"><Componente_Icono Nombre="edificio" :Tamano="18" /></span>
                <span>{{ Opcion.Nombre }}</span>
                <Componente_Icono Nombre="flecha" :Tamano="18" />
            </button>
        </div>
        <form v-else class="stack" novalidate @submit.prevent="Iniciar_Sesion">
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
.choose { gap: 10px; padding: 16px; border-radius: var(--radius-lg); background: var(--blue-50); border: 1px solid var(--blue-100); animation: aparecer-escala 0.3s var(--ease) both; }
.option {
  display: flex; align-items: center; gap: 12px; width: 100%; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--line);
  background: var(--surface); font: inherit; font-weight: 700; color: var(--navy-950); text-align: left; cursor: pointer;
  transition: border-color 0.2s, translate 0.2s var(--ease);
}
.option span:nth-child(2) { flex: 1; }
.option:hover:not(:disabled) { border-color: var(--navy-700); translate: 2px 0; }
.foot { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--muted); border-top: 1px solid var(--line); padding-top: 18px; }
</style>
