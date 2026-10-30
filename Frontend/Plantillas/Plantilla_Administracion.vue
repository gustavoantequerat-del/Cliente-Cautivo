<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Componente_Icono from '../Componentes/Componente_Icono.vue';
import Componente_Mensaje_Flash from '../Componentes/Componente_Mensaje_Flash.vue';
import Componente_Marca_Blockfinity from '../Componentes/Componente_Marca_Blockfinity.vue';

const Pagina = usePage();
const Usuario_Actual = computed(() => Pagina.props.Usuario_Actual);

const Grupos_Menu = [
    { Titulo: 'General', Enlaces: [
        { Url: '/admin', Etiqueta: 'Resumen', Coincidencia_Exacta: true },
        { Url: '/admin/solicitudes-horas', Etiqueta: 'Solicitudes de horas' },
        { Url: '/admin/auditoria', Etiqueta: 'Auditoría' },
    ] },
    { Titulo: 'Cuentas', Enlaces: [
        { Url: '/admin/instituciones', Etiqueta: 'Instituciones' },
        { Url: '/admin/usuarios', Etiqueta: 'Usuarios y credenciales' },
        { Url: '/admin/unidades', Etiqueta: 'Unidades de negocio' },
    ] },
    { Titulo: 'Relación', Enlaces: [
        { Url: '/admin/proyectos', Etiqueta: 'Proyectos' },
        { Url: '/admin/fases', Etiqueta: 'Fases' },
        { Url: '/admin/bitacora', Etiqueta: 'Bitácora' },
        { Url: '/admin/entregables', Etiqueta: 'Entregables' },
    ] },
    { Titulo: 'Contenido', Enlaces: [
        { Url: '/admin/videos', Etiqueta: 'Videos y webinars' },
        { Url: '/admin/research', Etiqueta: 'Research' },
    ] },
    { Titulo: 'Administración', Enlaces: [
        { Url: '/admin/facturas', Etiqueta: 'Facturas' },
        { Url: '/admin/contratos', Etiqueta: 'Contratos' },
        { Url: '/admin/horas', Etiqueta: 'Movimientos de horas' },
    ] },
];

const Ruta_Actual = computed(() => Pagina.url.split('?')[0]);
const Es_Enlace_Activo = (Enlace) => (Enlace.Coincidencia_Exacta ? Ruta_Actual.value === Enlace.Url : Ruta_Actual.value.startsWith(Enlace.Url));
</script>

<template>
    <div :class="$style.shell">
        <aside :class="$style.sidebar" aria-label="Menú del CMS">
            <Link href="/admin" :class="$style.brand"><Componente_Marca_Blockfinity /><span :class="$style.tag">CMS</span></Link>
            <nav :class="$style.nav">
                <div v-for="Grupo in Grupos_Menu" :key="Grupo.Titulo" :class="$style.group">
                    <span :class="$style.heading">{{ Grupo.Titulo }}</span>
                    <Link v-for="Enlace in Grupo.Enlaces" :key="Enlace.Url" :href="Enlace.Url" :class="[$style.link, Es_Enlace_Activo(Enlace) && $style.active]"
                          :aria-current="Es_Enlace_Activo(Enlace) ? 'page' : undefined">{{ Enlace.Etiqueta }}</Link>
                </div>
            </nav>
        </aside>
        <div :class="$style.main">
            <header :class="$style.topbar">
                <span class="eyebrow">Gestión de contenidos · Blockfinity</span>
                <span :class="$style.spacer" />
                <span :class="$style.who">{{ Usuario_Actual?.Nombre }}</span>
                <button type="button" class="btn btn-secondary btn-sm" @click="router.post('/logout')">
                    <Componente_Icono Nombre="salir" :Tamano="16" />Salir
                </button>
            </header>
            <main :class="$style.content">
                <Componente_Mensaje_Flash />
                <slot />
            </main>
        </div>
    </div>
</template>

<style module>
.shell { min-height: 100vh; display: flex; flex-wrap: wrap; }
.sidebar { flex: 1 1 240px; max-width: 100%; background: var(--navy-950); padding: 22px 14px; display: flex; flex-direction: column; gap: 18px; }
@media (min-width: 900px) { .sidebar { flex: 0 0 240px; min-height: 100vh; } }
.brand { display: flex; align-items: center; gap: 10px; padding: 0 8px; text-decoration: none; }
.tag { font-size: 11px; font-weight: 800; letter-spacing: 0.12em; padding: 3px 8px; border-radius: 6px; background: var(--navy-700); color: #fff; }
.nav { display: flex; flex-direction: column; gap: 10px; }
.group { display: flex; flex-direction: column; gap: 2px; }
.heading { padding: 6px 10px; font-size: 11px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--side-label); }
.link { display: flex; align-items: center; min-height: 38px; padding: 0 10px; border-radius: 8px; font-size: 14px; font-weight: 600; color: var(--side-text); text-decoration: none; }
.link:hover { background: var(--navy-850); color: #fff; }
.active, .active:hover { background: var(--navy-700); color: #fff; }
.main { flex: 999 1 560px; min-width: 0; display: flex; flex-direction: column; }
.topbar { display: flex; align-items: center; gap: 12px; padding: 12px 32px; background: var(--surface); border-bottom: 1px solid var(--line); flex-wrap: wrap; }
.spacer { flex: 1; }
.who { font-size: 14px; font-weight: 700; }
.content { padding: 28px 32px 56px; display: flex; flex-direction: column; gap: 24px; max-width: 1280px; width: 100%; }
@media (max-width: 700px) { .content { padding: 20px 16px; } .topbar { padding: 10px 16px; } }
</style>
