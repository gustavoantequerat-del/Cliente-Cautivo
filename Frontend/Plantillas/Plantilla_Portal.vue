<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Componente_Icono from '../Componentes/Componente_Icono.vue';
import Componente_Mensaje_Flash from '../Componentes/Componente_Mensaje_Flash.vue';
import Componente_Marca_Blockfinity from '../Componentes/Componente_Marca_Blockfinity.vue';
import { Construir_Variables_Marca } from '../Utilidades/Paleta_Marca.js';

const Pagina = usePage();
const Usuario_Actual = computed(() => Pagina.props.Usuario_Actual);
const Institucion_Actual = computed(() => Pagina.props.Institucion_Actual);
const Ejecutivo_Cuenta = computed(() => Pagina.props.Ejecutivo_Cuenta);
const Menu_Movil_Abierto = ref(false);

// Cierra el menú móvil al navegar
watch(() => Pagina.url, () => { Menu_Movil_Abierto.value = false; });

const Tiene_Research = computed(() => (Institucion_Actual.value?.Codigos_Unidades ?? []).includes('RESEARCH'));
// Colores de la institución sobre todo el portal (si no tiene, queda la marca Blockfinity)
const Variables_Marca = computed(() => Construir_Variables_Marca(Institucion_Actual.value?.Color_Primario, Institucion_Actual.value?.Color_Secundario));

// El usuario institucional ve lo esencial; el administrador delegado, además, la gestión de su institución
const Elementos_Menu = computed(() => [
    { Url: '/', Etiqueta: 'Inicio', Icono: 'inicio', Coincidencia_Exacta: true },
    { Url: '/ecosistema', Etiqueta: 'Ecosistema Blockfinity', Icono: 'capas' },
    { Url: '/academia', Etiqueta: 'Píldoras y webinars', Icono: 'reproducir' },
    ...(Tiene_Research.value ? [{ Url: '/research', Etiqueta: 'Research', Icono: 'grafico' }] : []),
    { Url: '/certificados', Etiqueta: 'Mis acreditaciones', Icono: 'certificado' },
    { Url: '/credencial', Etiqueta: 'Mi credencial', Icono: 'credencial' },
    ...(Usuario_Actual.value?.Es_Administrador_Delegado ? [
        { Titulo: 'Gestión institucional' },
        { Url: '/entregables', Etiqueta: 'Entregables', Icono: 'boveda' },
        { Url: '/facturacion', Etiqueta: 'Facturación', Icono: 'facturacion' },
        { Url: '/bolsa-de-horas', Etiqueta: 'Bolsa de horas', Icono: 'reloj' },
    ] : []),
]);

const Es_Enlace_Activo = (Elemento) => (Elemento.Coincidencia_Exacta ? Pagina.url === Elemento.Url : Pagina.url.startsWith(Elemento.Url));
const Cerrar_Sesion = () => router.post('/logout');
</script>

<template>
    <div :class="$style.shell" :style="Variables_Marca">
        <a href="#contenido" :class="$style.skip">Saltar al contenido</a>

        <aside :class="[$style.sidebar, Menu_Movil_Abierto && $style.open]" aria-label="Navegación del portal">
            <div :class="$style.sideTop">
                <!-- Marca compartida: el logo de la institución y, al lado, el de Blockfinity -->
                <Link href="/" :class="$style.brand" :aria-label="`${Institucion_Actual?.Nombre ?? 'Portal'} · Inicio`">
                    <template v-if="Institucion_Actual?.Url_Logo">
                        <span :class="$style.logoBox"><img :src="Institucion_Actual.Url_Logo" :alt="Institucion_Actual.Nombre" :class="$style.logo"></span>
                        <span :class="$style.brandDivider" aria-hidden="true" />
                        <Componente_Marca_Blockfinity Compacta />
                    </template>
                    <Componente_Marca_Blockfinity v-else />
                </Link>
                <button type="button" :class="$style.closeBtn" aria-label="Cerrar menú" @click="Menu_Movil_Abierto = false">
                    <Componente_Icono Nombre="cerrar" />
                </button>
            </div>
            <div :class="$style.account">
                <span :class="$style.accountLabel">PORTAL CLIENTE</span>
                <span :class="$style.accountName">{{ Institucion_Actual?.Nombre }}</span>
                <span :class="$style.role">{{ Usuario_Actual?.Nombre_Rol }}</span>
            </div>
            <nav :class="$style.nav">
                <template v-for="(Elemento, Posicion) in Elementos_Menu" :key="Posicion">
                    <span v-if="Elemento.Titulo" :class="$style.heading">{{ Elemento.Titulo }}</span>
                    <Link v-else :href="Elemento.Url" :class="[$style.link, Es_Enlace_Activo(Elemento) && $style.active]"
                          :aria-current="Es_Enlace_Activo(Elemento) ? 'page' : undefined">
                        <Componente_Icono :Nombre="Elemento.Icono" :Tamano="18" />{{ Elemento.Etiqueta }}
                    </Link>
                </template>
            </nav>
            <div v-if="Ejecutivo_Cuenta" :class="$style.manager">
                <span :class="$style.accountLabel">SU EJECUTIVA DE CUENTA</span>
                <div class="row">
                    <span :class="[$style.avatar, $style.avatarManager]">{{ Ejecutivo_Cuenta.Iniciales }}</span>
                    <span :class="$style.person">
                        <strong>{{ Ejecutivo_Cuenta.Nombre }}</strong>
                        <span>{{ Ejecutivo_Cuenta.Cargo }}</span>
                    </span>
                </div>
                <a v-if="Ejecutivo_Cuenta.Enlace_Agenda" :href="Ejecutivo_Cuenta.Enlace_Agenda" target="_blank" rel="noopener" :class="$style.managerBtn">Agendar reunión</a>
                <a v-else :href="`mailto:${Ejecutivo_Cuenta.Email}`" :class="$style.managerBtn">Escribir a {{ Ejecutivo_Cuenta.Nombre.split(' ')[0] }}</a>
            </div>
        </aside>
        <div v-if="Menu_Movil_Abierto" :class="$style.backdrop" @click="Menu_Movil_Abierto = false" />

        <div :class="$style.main">
            <header :class="$style.topbar">
                <button type="button" :class="$style.menuBtn" aria-label="Abrir menú" @click="Menu_Movil_Abierto = true">
                    <Componente_Icono Nombre="menu" />
                </button>
                <span v-if="Institucion_Actual?.Url_Logo" :class="[$style.logoBox, $style.logoTop]"><img :src="Institucion_Actual.Url_Logo" alt="" :class="$style.logo"></span>
                <span :class="$style.spacer" />
                <Link v-if="Usuario_Actual?.Numero_Credencial" href="/credencial" :class="$style.credPill">
                    <span :class="$style.dot" />Credencial #{{ Usuario_Actual.Numero_Credencial }}
                </Link>
                <Link href="/perfil" :class="$style.me" aria-label="Mi perfil">
                    <span :class="$style.avatar">{{ Usuario_Actual?.Iniciales }}</span>
                    <span :class="$style.person">
                        <strong>{{ Usuario_Actual?.Nombre }}</strong>
                        <span>{{ Usuario_Actual?.Area }}</span>
                    </span>
                </Link>
                <button type="button" :class="$style.iconBtn" aria-label="Cerrar sesión" title="Cerrar sesión" @click="Cerrar_Sesion">
                    <Componente_Icono Nombre="salir" />
                </button>
            </header>
            <main id="contenido" :class="$style.content">
                <Componente_Mensaje_Flash />
                <!-- La clave cambia en cada página: así cada pantalla entra con su animación -->
                <div :key="Pagina.component" class="animar-entrada" :class="$style.page"><slot /></div>
            </main>
        </div>
    </div>
</template>

<style module>
.shell { min-height: 100vh; display: flex; }
.skip { position: absolute; left: -9999px; top: 8px; z-index: 50; background: #fff; padding: 8px 12px; border-radius: 8px; }
.skip:focus { left: 8px; }

.sidebar {
  width: 272px; flex: none; background: linear-gradient(180deg, var(--navy-950) 0%, var(--navy-900) 100%); padding: 24px 16px; display: flex; flex-direction: column; gap: 20px;
  position: sticky; top: 0; height: 100vh; overflow-y: auto;
}
.sideTop { display: flex; align-items: center; justify-content: space-between; padding: 0 8px; }
.brand { display: flex; align-items: center; gap: 10px; text-decoration: none; min-width: 0; }
.logoBox { display: inline-flex; align-items: center; justify-content: center; height: 44px; max-width: 120px; padding: 6px 10px; border-radius: 10px; background: #fff; flex: none; }
.logo { display: block; max-height: 32px; max-width: 100px; object-fit: contain; }
.brandDivider { width: 1px; height: 28px; background: rgba(255, 255, 255, 0.25); flex: none; }
.logoTop { display: none; height: 38px; border: 1px solid var(--line); }
.closeBtn { display: none; background: none; border: 0; color: #fff; width: 44px; height: 44px; cursor: pointer; }
.account { margin: 0 8px; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--navy-line); display: flex; flex-direction: column; gap: 2px; }
.accountLabel { font-size: 11px; font-weight: 700; letter-spacing: 0.14em; color: var(--side-label); }
.accountName { font-size: 15px; font-weight: 700; color: #fff; }
.role { align-self: flex-start; margin-top: 6px; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 999px; background: rgba(90, 169, 230, 0.18); color: #CFE0F5; }
.nav { display: flex; flex-direction: column; gap: 2px; }
.heading { padding: 14px 12px 6px; font-size: 11px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--side-label); }
.link {
  position: relative; display: flex; align-items: center; gap: 12px; min-height: 44px; padding: 0 12px; border-radius: 12px;
  text-decoration: none; font-size: 14px; font-weight: 600; color: var(--side-text);
  transition: background 0.2s, color 0.2s, transform 0.2s var(--ease);
}
.link:hover { background: var(--navy-850); color: #fff; transform: translateX(3px); }
.active, .active:hover { background: linear-gradient(90deg, var(--navy-700), var(--navy-800)); color: #fff; box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25); }
.active::before { content: ''; position: absolute; left: -16px; top: 10px; bottom: 10px; width: 4px; border-radius: 0 4px 4px 0; background: var(--sky-400); }

.manager { margin-top: auto; padding: 16px; border-radius: 14px; background: var(--navy-850); display: flex; flex-direction: column; gap: 12px; }
.managerBtn {
  display: flex; align-items: center; justify-content: center; min-height: 44px; border-radius: 10px;
  border: 1px solid var(--navy-500); color: #fff; text-decoration: none; font-size: 14px; font-weight: 700;
}
.managerBtn:hover { background: var(--navy-800); color: #fff; }
.manager .person strong { color: #fff; }
.manager .person span { color: #9AA6B8; }

.avatar {
  width: 40px; height: 40px; border-radius: 50%; background: var(--gradient-primary); color: #fff; flex: none;
  box-shadow: 0 0 0 3px #fff, 0 0 0 4px var(--line);
  display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;
}
.avatarManager { background: var(--navy-500); }
.person { display: flex; flex-direction: column; min-width: 0; }
.person strong { font-size: 14px; color: var(--navy-950); }
.person span { font-size: 12px; color: var(--muted); }

.main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.topbar {
  display: flex; align-items: center; gap: 12px; padding: 12px 40px; background: rgba(255, 255, 255, 0.82);
  backdrop-filter: blur(12px); border-bottom: 1px solid var(--line); position: sticky; top: 0; z-index: 10;
}
.spacer { flex: 1; }
.menuBtn { display: none; }
.iconBtn, .menuBtn {
  width: 44px; height: 44px; border-radius: 10px; border: 1px solid var(--line); background: var(--surface);
  align-items: center; justify-content: center; cursor: pointer; color: var(--ink);
}
.iconBtn { display: inline-flex; transition: background 0.2s, color 0.2s, border-color 0.2s; }
.iconBtn:hover { background: var(--danger-bg); color: var(--danger-fg); border-color: #E3B4AF; }
.credPill {
  display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 14px; border-radius: 10px;
  background: var(--blue-100); color: var(--navy-900); font-size: 13px; font-weight: 700; text-decoration: none;
}
.dot { width: 8px; height: 8px; border-radius: 50%; background: #3E9B66; box-shadow: 0 0 0 3px rgba(62, 155, 102, 0.2); }
.me { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; }
.content { padding: 32px 40px 56px; width: 100%; max-width: 1280px; margin: 0 auto; }
.page { display: flex; flex-direction: column; gap: 32px; }
.backdrop { display: none; }

@media (max-width: 960px) {
  .sidebar { position: fixed; left: 0; top: 0; bottom: 0; z-index: 40; transform: translateX(-100%); transition: transform 0.2s; }
  .open { transform: none; }
  .closeBtn { display: inline-flex; align-items: center; justify-content: center; }
  .backdrop { display: block; position: fixed; inset: 0; background: rgba(10, 22, 40, 0.45); z-index: 30; }
  .menuBtn { display: inline-flex; }
  .logoTop { display: inline-flex; }
  .topbar { padding: 10px 16px; }
  .content { padding: 20px 16px 40px; }
  .page { gap: 24px; }
  .credPill, .me .person { display: none; }
}
</style>
