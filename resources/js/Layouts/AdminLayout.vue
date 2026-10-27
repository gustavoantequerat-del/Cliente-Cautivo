<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Icon from '../Components/Icon.vue';
import FlashMessage from '../Components/FlashMessage.vue';
import BrandMark from '../Components/BrandMark.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const groups = [
    { heading: 'General', items: [
        { href: '/admin', label: 'Resumen', exact: true },
        { href: '/admin/solicitudes-horas', label: 'Solicitudes de horas' },
        { href: '/admin/auditoria', label: 'Auditoría' },
    ] },
    { heading: 'Cuentas', items: [
        { href: '/admin/instituciones', label: 'Instituciones' },
        { href: '/admin/usuarios', label: 'Usuarios y credenciales' },
        { href: '/admin/unidades', label: 'Unidades de negocio' },
    ] },
    { heading: 'Relación', items: [
        { href: '/admin/proyectos', label: 'Proyectos' },
        { href: '/admin/fases', label: 'Fases' },
        { href: '/admin/bitacora', label: 'Bitácora' },
        { href: '/admin/entregables', label: 'Entregables' },
    ] },
    { heading: 'Contenido', items: [
        { href: '/admin/videos', label: 'Videos y webinars' },
        { href: '/admin/research', label: 'Research' },
    ] },
    { heading: 'Administración', items: [
        { href: '/admin/facturas', label: 'Facturas' },
        { href: '/admin/contratos', label: 'Contratos' },
        { href: '/admin/horas', label: 'Movimientos de horas' },
    ] },
];

const path = computed(() => page.url.split('?')[0]);
const isActive = (i) => (i.exact ? path.value === i.href : path.value.startsWith(i.href));
</script>

<template>
    <div :class="$style.shell">
        <aside :class="$style.sidebar" aria-label="Menú del CMS">
            <Link href="/admin" :class="$style.brand"><BrandMark /><span :class="$style.tag">CMS</span></Link>
            <nav :class="$style.nav">
                <div v-for="g in groups" :key="g.heading" :class="$style.group">
                    <span :class="$style.heading">{{ g.heading }}</span>
                    <Link v-for="i in g.items" :key="i.href" :href="i.href" :class="[$style.link, isActive(i) && $style.active]"
                          :aria-current="isActive(i) ? 'page' : undefined">{{ i.label }}</Link>
                </div>
            </nav>
        </aside>
        <div :class="$style.main">
            <header :class="$style.topbar">
                <span class="eyebrow">Gestión de contenidos · Blockfinity</span>
                <span :class="$style.spacer" />
                <span :class="$style.who">{{ user?.name }}</span>
                <button type="button" class="btn btn-secondary btn-sm" @click="router.post('/logout')">
                    <Icon name="logout" :size="16" />Salir
                </button>
            </header>
            <main :class="$style.content">
                <FlashMessage />
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
