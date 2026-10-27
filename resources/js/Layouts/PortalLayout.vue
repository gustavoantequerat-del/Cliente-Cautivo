<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Icon from '../Components/Icon.vue';
import FlashMessage from '../Components/FlashMessage.vue';
import BrandMark from '../Components/BrandMark.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const institution = computed(() => page.props.institution);
const manager = computed(() => page.props.accountManager);
const units = computed(() => institution.value?.units ?? []);
const menuOpen = ref(false);

// Cierra el menú móvil al navegar
watch(() => page.url, () => { menuOpen.value = false; });

const nav = computed(() => [
    { heading: 'Mi cuenta' },
    { href: '/', label: 'Inicio', icon: 'home', match: (u) => u === '/' },
    { href: '/proyectos', label: 'Proyectos', icon: 'projects' },
    { href: '/entregables', label: 'Historial de entregables', icon: 'vault' },
    { href: '/facturacion', label: 'Facturación y contratos', icon: 'billing' },
    { href: '/bolsa-de-horas', label: 'Bolsa de horas', icon: 'clock' },
    { heading: 'Conocimiento' },
    ...(units.value.includes('RESEARCH') ? [{ href: '/research', label: 'Research e inteligencia', icon: 'research' }] : []),
    { href: '/academia', label: 'Píldoras y webinars', icon: 'play' },
    { heading: 'Ecosistema' },
    { href: '/credencial', label: 'Credencial y beneficios', icon: 'credential' },
]);

const isActive = (item) => (item.match ? item.match(page.url) : page.url.startsWith(item.href));
const logout = () => router.post('/logout');
</script>

<template>
    <div :class="$style.shell">
        <a href="#contenido" :class="$style.skip">Saltar al contenido</a>

        <aside :class="[$style.sidebar, menuOpen && $style.open]" aria-label="Navegación del portal">
            <div :class="$style.sideTop">
                <Link href="/" :class="$style.brand" aria-label="Blockfinity Advisors · Inicio"><BrandMark /></Link>
                <button type="button" :class="$style.closeBtn" aria-label="Cerrar menú" @click="menuOpen = false">
                    <Icon name="close" />
                </button>
            </div>
            <div :class="$style.account">
                <span :class="$style.accountLabel">PORTAL CLIENTE</span>
                <span :class="$style.accountName">{{ institution?.name }}</span>
            </div>
            <nav :class="$style.nav">
                <template v-for="(item, i) in nav" :key="i">
                    <span v-if="item.heading" :class="$style.heading">{{ item.heading }}</span>
                    <Link v-else :href="item.href" :class="[$style.link, isActive(item) && $style.active]"
                          :aria-current="isActive(item) ? 'page' : undefined">
                        <Icon :name="item.icon" :size="18" />{{ item.label }}
                    </Link>
                </template>
                <Link v-if="user?.isAdmin" href="/admin" :class="$style.link"><Icon name="grid" :size="18" />Ir al CMS</Link>
            </nav>
            <div v-if="manager" :class="$style.manager">
                <span :class="$style.accountLabel">SU EJECUTIVA DE CUENTA</span>
                <div class="row">
                    <span :class="[$style.avatar, $style.avatarManager]">{{ manager.initials }}</span>
                    <span :class="$style.person">
                        <strong>{{ manager.name }}</strong>
                        <span>{{ manager.position }}</span>
                    </span>
                </div>
                <a v-if="manager.bookingUrl" :href="manager.bookingUrl" target="_blank" rel="noopener" :class="$style.managerBtn">Agendar reunión</a>
                <a v-else :href="`mailto:${manager.email}`" :class="$style.managerBtn">Escribir a {{ manager.name.split(' ')[0] }}</a>
            </div>
        </aside>
        <div v-if="menuOpen" :class="$style.backdrop" @click="menuOpen = false" />

        <div :class="$style.main">
            <header :class="$style.topbar">
                <button type="button" :class="$style.menuBtn" aria-label="Abrir menú" @click="menuOpen = true">
                    <Icon name="menu" />
                </button>
                <span :class="$style.spacer" />
                <Link v-if="user?.credentialNumber" href="/credencial" :class="$style.credPill">
                    <span :class="$style.dot" />Credencial #{{ user.credentialNumber }}
                </Link>
                <Link href="/perfil" :class="$style.me" aria-label="Mi perfil">
                    <span :class="$style.avatar">{{ user?.initials }}</span>
                    <span :class="$style.person">
                        <strong>{{ user?.name }}</strong>
                        <span>{{ user?.area }}</span>
                    </span>
                </Link>
                <button type="button" :class="$style.iconBtn" aria-label="Cerrar sesión" title="Cerrar sesión" @click="logout">
                    <Icon name="logout" />
                </button>
            </header>
            <main id="contenido" :class="$style.content">
                <FlashMessage />
                <slot />
            </main>
        </div>
    </div>
</template>

<style module>
.shell { min-height: 100vh; display: flex; }
.skip { position: absolute; left: -9999px; top: 8px; z-index: 50; background: #fff; padding: 8px 12px; border-radius: 8px; }
.skip:focus { left: 8px; }

.sidebar {
  width: 272px; flex: none; background: var(--navy-950); padding: 24px 16px; display: flex; flex-direction: column; gap: 20px;
  position: sticky; top: 0; height: 100vh; overflow-y: auto;
}
.sideTop { display: flex; align-items: center; justify-content: space-between; padding: 0 8px; }
.brand { display: block; text-decoration: none; }
.closeBtn { display: none; background: none; border: 0; color: #fff; width: 44px; height: 44px; cursor: pointer; }
.account { margin: 0 8px; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--navy-line); display: flex; flex-direction: column; gap: 2px; }
.accountLabel { font-size: 11px; font-weight: 700; letter-spacing: 0.14em; color: var(--side-label); }
.accountName { font-size: 15px; font-weight: 700; color: #fff; }
.nav { display: flex; flex-direction: column; gap: 2px; }
.heading { padding: 14px 12px 6px; font-size: 11px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--side-label); }
.link {
  display: flex; align-items: center; gap: 12px; min-height: 44px; padding: 0 12px; border-radius: 10px;
  text-decoration: none; font-size: 14px; font-weight: 600; color: var(--side-text);
}
.link:hover { background: var(--navy-850); color: #fff; }
.active, .active:hover { background: var(--navy-700); color: #fff; }

.manager { margin-top: auto; padding: 16px; border-radius: 14px; background: var(--navy-850); display: flex; flex-direction: column; gap: 12px; }
.managerBtn {
  display: flex; align-items: center; justify-content: center; min-height: 44px; border-radius: 10px;
  border: 1px solid var(--navy-500); color: #fff; text-decoration: none; font-size: 14px; font-weight: 700;
}
.managerBtn:hover { background: var(--navy-800); color: #fff; }
.manager .person strong { color: #fff; }
.manager .person span { color: #9AA6B8; }

.avatar {
  width: 40px; height: 40px; border-radius: 50%; background: var(--navy-800); color: #fff; flex: none;
  display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;
}
.avatarManager { background: var(--navy-500); }
.person { display: flex; flex-direction: column; min-width: 0; }
.person strong { font-size: 14px; color: var(--navy-950); }
.person span { font-size: 12px; color: var(--muted); }

.main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.topbar {
  display: flex; align-items: center; gap: 12px; padding: 12px 40px; background: var(--surface);
  border-bottom: 1px solid var(--line); position: sticky; top: 0; z-index: 10;
}
.spacer { flex: 1; }
.menuBtn { display: none; }
.iconBtn, .menuBtn {
  width: 44px; height: 44px; border-radius: 10px; border: 1px solid var(--line); background: var(--surface);
  align-items: center; justify-content: center; cursor: pointer; color: var(--ink);
}
.iconBtn { display: inline-flex; }
.credPill {
  display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 14px; border-radius: 10px;
  background: var(--blue-100); color: var(--navy-900); font-size: 13px; font-weight: 700; text-decoration: none;
}
.dot { width: 8px; height: 8px; border-radius: 50%; background: var(--navy-700); }
.me { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; }
.content { padding: 32px 40px 56px; width: 100%; max-width: 1280px; display: flex; flex-direction: column; gap: 32px; }
.backdrop { display: none; }

@media (max-width: 960px) {
  .sidebar { position: fixed; left: 0; top: 0; bottom: 0; z-index: 40; transform: translateX(-100%); transition: transform 0.2s; }
  .open { transform: none; }
  .closeBtn { display: inline-flex; align-items: center; justify-content: center; }
  .backdrop { display: block; position: fixed; inset: 0; background: rgba(10, 22, 40, 0.45); z-index: 30; }
  .menuBtn { display: inline-flex; }
  .topbar { padding: 10px 16px; }
  .content { padding: 20px 16px 40px; gap: 24px; }
  .credPill, .me .person { display: none; }
}
</style>
