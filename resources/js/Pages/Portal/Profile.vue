<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import PageHeader from '../../Components/PageHeader.vue';

const user = usePage().props.auth.user;
const form = useForm({ current_password: '', password: '', password_confirmation: '' });
const submit = () => form.put('/perfil/contrasena', { preserveScroll: true, onSuccess: () => form.reset() });
</script>

<template>
    <Head title="Mi perfil" />
    <PageHeader title="Mi perfil" />
    <div class="grid-2">
        <section class="card stack">
            <h2>Datos</h2>
            <dl :class="$style.dl">
                <div><dt>Nombre</dt><dd>{{ user.name }}</dd></div>
                <div><dt>Correo</dt><dd>{{ user.email }}</dd></div>
                <div><dt>Área</dt><dd>{{ user.area ?? '—' }}</dd></div>
            </dl>
            <p class="muted">Para cambiar estos datos, contacte al administrador de su institución.</p>
        </section>
        <form class="card stack" @submit.prevent="submit">
            <h2>Cambiar contraseña</h2>
            <div class="field">
                <label for="current">Contraseña actual</label>
                <input id="current" v-model="form.current_password" type="password" class="input" autocomplete="current-password" required>
                <span v-if="form.errors.current_password" class="error">{{ form.errors.current_password }}</span>
            </div>
            <div class="field">
                <label for="new">Nueva contraseña</label>
                <input id="new" v-model="form.password" type="password" class="input" autocomplete="new-password" required>
                <span class="help">Mínimo 10 caracteres, con letras y números.</span>
                <span v-if="form.errors.password" class="error">{{ form.errors.password }}</span>
            </div>
            <div class="field">
                <label for="confirm">Repita la nueva contraseña</label>
                <input id="confirm" v-model="form.password_confirmation" type="password" class="input" autocomplete="new-password" required>
            </div>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">Guardar</button>
        </form>
    </div>
</template>

<style module>
.dl { margin: 0; display: flex; flex-direction: column; gap: 12px; }
.dl div { display: flex; flex-direction: column; }
.dl dt { font-size: 12px; color: var(--muted); }
.dl dd { margin: 0; font-weight: 700; }
</style>
