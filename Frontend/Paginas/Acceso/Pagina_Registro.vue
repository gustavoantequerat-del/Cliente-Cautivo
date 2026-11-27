<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Plantilla_Acceso from '../../Plantillas/Plantilla_Acceso.vue';
import Componente_Campo_Contrasena from '../../Componentes/Componente_Campo_Contrasena.vue';
import Componente_Indicador_Contrasena from '../../Componentes/Componente_Indicador_Contrasena.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

// Registro con el link de una institución: crea una cuenta de usuario institucional solo en esa institución
const Propiedades = defineProps({ Institucion: Object, Codigo_Registro: String });
const Formulario = useForm({ nombre: '', email: '', area: '', cargo: '', telefono: '', password: '', password_confirmation: '' });
const Contrasenas_Distintas = computed(() => Formulario.password_confirmation && Formulario.password !== Formulario.password_confirmation);
const Registrar = () => Formulario.post(`/registro/${Propiedades.Codigo_Registro}`, { onFinish: () => Formulario.reset('password', 'password_confirmation') });
// El nombre solo admite letras y el teléfono solo dígitos (con + inicial), igual que valida el Backend
const Filtrar_Nombre = (Evento) => { Formulario.nombre = Evento.target.value = Evento.target.value.replace(/[^\p{L}\s'.-]/gu, ''); };
const Filtrar_Telefono = (Evento) => { Formulario.telefono = Evento.target.value = Evento.target.value.replace(/(?!^\+)[^\d]/g, '').slice(0, 16); };
</script>

<template>
    <Head :title="Institucion ? `Registro · ${Institucion.Nombre}` : 'Registro'" />
    <Plantilla_Acceso :Antetitulo="Institucion ? 'Invitación' : 'Portal Cliente'"
                      :Titulo="Institucion ? `Cree su cuenta en el portal de ${Institucion.Nombre}.` : 'Todo su trabajo con Blockfinity, en un solo lugar.'"
                      :Url_Logo_Institucion="Institucion?.Url_Logo">
        <template v-if="Institucion">
            <div class="stack" :class="$style.tight">
                <h2 :class="$style.h">Crear mi cuenta</h2>
                <p class="muted">Para <strong>{{ Institucion.Nombre }}</strong>. Si ya tiene cuenta aquí, <Link href="/login">ingrese</Link>.</p>
            </div>
            <form class="stack" novalidate @submit.prevent="Registrar">
                <div class="field">
                    <label for="nombre">Nombre completo *</label>
                    <input id="nombre" :value="Formulario.nombre" class="input" autocomplete="name" maxlength="120" required autofocus
                           :aria-invalid="Formulario.errors.nombre ? 'true' : undefined" @input="Filtrar_Nombre">
                    <span v-if="Formulario.errors.nombre" class="error">{{ Formulario.errors.nombre }}</span>
                </div>
                <div class="field">
                    <label for="email">Correo *</label>
                    <input id="email" v-model.trim="Formulario.email" class="input" type="email" inputmode="email" autocomplete="email"
                           placeholder="nombre@institucion.com" maxlength="255" required :aria-invalid="Formulario.errors.email ? 'true' : undefined">
                    <span v-if="Formulario.errors.email" class="error">{{ Formulario.errors.email }}</span>
                </div>
                <div :class="$style.pair">
                    <div class="field">
                        <label for="area">Área</label>
                        <input id="area" v-model.trim="Formulario.area" class="input" autocomplete="organization-title" maxlength="120">
                    </div>
                    <div class="field">
                        <label for="cargo">Cargo</label>
                        <input id="cargo" v-model.trim="Formulario.cargo" class="input" maxlength="120">
                    </div>
                </div>
                <div class="field">
                    <label for="telefono">Teléfono</label>
                    <input id="telefono" :value="Formulario.telefono" class="input" type="tel" inputmode="tel" autocomplete="tel" placeholder="+59170000000" @input="Filtrar_Telefono">
                    <span v-if="Formulario.errors.telefono" class="error">{{ Formulario.errors.telefono }}</span>
                </div>
                <div class="field">
                    <label for="password">Contraseña *</label>
                    <Componente_Campo_Contrasena Id="password" v-model="Formulario.password" Autocompletar="new-password" :Tiene_Error="!!Formulario.errors.password" />
                    <Componente_Indicador_Contrasena :Contrasena="Formulario.password" />
                    <span v-if="Formulario.errors.password" class="error">{{ Formulario.errors.password }}</span>
                </div>
                <div class="field">
                    <label for="password_confirmation">Repita la contraseña *</label>
                    <Componente_Campo_Contrasena Id="password_confirmation" v-model="Formulario.password_confirmation" Autocompletar="new-password" :Tiene_Error="!!Contrasenas_Distintas" />
                    <span v-if="Contrasenas_Distintas" class="error">Las contraseñas no coinciden.</span>
                </div>
                <button class="btn btn-primary" :class="$style.full" type="submit" :disabled="Formulario.processing || Contrasenas_Distintas">
                    <Componente_Icono :Nombre="Formulario.processing ? 'cargando' : 'agregar'" :Tamano="18" :class="Formulario.processing && 'girando'" />
                    {{ Formulario.processing ? 'Creando su cuenta…' : 'Crear mi cuenta' }}
                </button>
            </form>
            <p :class="$style.foot"><Componente_Icono Nombre="candado" :Tamano="16" />Su cuenta y sus datos son solo de esta institución.</p>
        </template>
        <div v-else class="stack" :class="$style.closed">
            <span class="burbuja"><Componente_Icono Nombre="candado" /></span>
            <h2 :class="$style.h">Este link no está disponible</h2>
            <p class="muted">El registro está cerrado o el link cambió. Pida a su institución el link actualizado.</p>
            <Link href="/login" class="btn btn-secondary">Ir a ingresar</Link>
        </div>
    </Plantilla_Acceso>
</template>

<style module>
.tight { gap: 6px; }
.h { font-size: 28px; }
.pair { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 160px), 1fr)); }
.full { width: 100%; min-height: 52px; font-size: 16px; }
.foot { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--muted); border-top: 1px solid var(--line); padding-top: 18px; }
.closed { align-items: flex-start; gap: 14px; }
</style>
