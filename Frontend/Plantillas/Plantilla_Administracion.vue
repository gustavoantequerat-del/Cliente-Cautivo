<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Componente_Icono from '../Componentes/Componente_Icono.vue';
import Componente_Mensaje_Flash from '../Componentes/Componente_Mensaje_Flash.vue';
import Componente_Marca_Blockfinity from '../Componentes/Componente_Marca_Blockfinity.vue';

const Pagina = usePage();
const Usuario_Actual = computed(() => Pagina.props.Usuario_Actual);

// Solo_Superadmin: contenido global que el usuario ejecutivo no gestiona
const Todos_Los_Grupos = [
    { Titulo: 'General', Enlaces: [
        { Url: '/admin', Etiqueta: 'Resumen', Icono: 'cuadricula', Coincidencia_Exacta: true },
        { Url: '/admin/solicitudes-horas', Etiqueta: 'Solicitudes de horas', Icono: 'reloj' },
        { Url: '/admin/auditoria', Etiqueta: 'Auditoría', Icono: 'escudo', Solo_Superadmin: true },
    ] },
    { Titulo: 'Cuentas', Enlaces: [
        { Url: '/admin/instituciones', Etiqueta: 'Instituciones', Icono: 'edificio' },
        { Url: '/admin/usuarios', Etiqueta: 'Usuarios', Icono: 'usuarios' },
        { Url: '/admin/unidades', Etiqueta: 'Unidades de negocio', Icono: 'capas', Solo_Superadmin: true },
    ] },
    { Titulo: 'Relación', Enlaces: [
        { Url: '/admin/proyectos', Etiqueta: 'Proyectos', Icono: 'proyectos' },
        { Url: '/admin/fases', Etiqueta: 'Fases', Icono: 'lista' },
        { Url: '/admin/bitacora', Etiqueta: 'Bitácora', Icono: 'libro' },
        { Url: '/admin/entregables', Etiqueta: 'Entregables', Icono: 'boveda' },
        { Url: '/admin/certificados', Etiqueta: 'Certificados', Icono: 'certificado' },
    ] },
    { Titulo: 'Contenido', Enlaces: [
        { Url: '/admin/videos', Etiqueta: 'Videos y webinars', Icono: 'reproducir', Solo_Superadmin: true },
        { Url: '/admin/research', Etiqueta: 'Research', Icono: 'grafico', Solo_Superadmin: true },
    ] },
    { Titulo: 'Administración', Enlaces: [
        { Url: '/admin/facturas', Etiqueta: 'Facturas', Icono: 'facturacion' },
        { Url: '/admin/contratos', Etiqueta: 'Contratos', Icono: 'archivo' },
        { Url: '/admin/horas', Etiqueta: 'Movimientos de horas', Icono: 'dinero' },
        { Url: '/admin/configuracion', Etiqueta: 'Configuración', Icono: 'lista', Solo_Superadmin: true },
    ] },
];

const Grupos_Menu = computed(() => Todos_Los_Grupos
    .map((Grupo) => ({ ...Grupo, Enlaces: Grupo.Enlaces.filter((Enlace) => Usuario_Actual.value?.Es_Superadmin || !Enlace.Solo_Superadmin) }))
    .filter((Grupo) => Grupo.Enlaces.length));
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
                          :aria-current="Es_Enlace_Activo(Enlace) ? 'page' : undefined"><Componente_Icono :Nombre="Enlace.Icono" :Tamano="17" />{{ Enlace.Etiqueta }}</Link>
                </div>
            </nav>
        </aside>
        <div :class="$style.main">
            <header :class="$style.topbar">
                <span :class="$style.secure"><Componente_Icono Nombre="candado" :Tamano="14" />Consola segura</span>
                <span :class="$style.spacer" />
                <span :class="$style.avatar">{{ Usuario_Actual?.Iniciales }}</span>
                <span :class="$style.who">{{ Usuario_Actual?.Nombre }}<small>{{ Usuario_Actual?.Nombre_Rol }}</small></span>
                <button type="button" class="btn btn-secondary btn-sm" @click="router.post('/logout')">
                    <Componente_Icono Nombre="salir" :Tamano="16" />Salir
                </button>
            </header>
            <main :class="$style.content">
                <Componente_Mensaje_Flash />
                <div :key="Pagina.component" class="animar-entrada" :class="$style.page"><slot /></div>
            </main>
        </div>
    </div>
</template>

<style module>
.shell { min-height: 100vh; display: flex; flex-wrap: wrap; }
.sidebar { flex: 1 1 240px; max-width: 100%; background: linear-gradient(180deg, #0B1220 0%, var(--navy-950) 100%); padding: 22px 14px; display: flex; flex-direction: column; gap: 18px; }
@media (min-width: 900px) { .sidebar { flex: 0 0 240px; min-height: 100vh; } }
.brand { display: flex; align-items: center; gap: 10px; padding: 0 8px; text-decoration: none; }
.tag { font-size: 11px; font-weight: 800; letter-spacing: 0.12em; padding: 3px 8px; border-radius: 6px; background: var(--navy-700); color: #fff; }
.nav { display: flex; flex-direction: column; gap: 10px; }
.group { display: flex; flex-direction: column; gap: 2px; }
.heading { padding: 6px 10px; font-size: 11px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--side-label); }
.link {
  display: flex; align-items: center; gap: 10px; min-height: 38px; padding: 0 10px; border-radius: 10px;
  font-size: 14px; font-weight: 600; color: var(--side-text); text-decoration: none;
  transition: background 0.2s, color 0.2s, transform 0.2s var(--ease);
}
.link:hover { background: var(--navy-850); color: #fff; transform: translateX(3px); }
.active, .active:hover { background: linear-gradient(90deg, #3B5B8C, var(--navy-700)); color: #fff; box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3); }
.main { flex: 999 1 560px; min-width: 0; display: flex; flex-direction: column; }
.topbar {
  display: flex; align-items: center; gap: 12px; padding: 12px 32px; flex-wrap: wrap; position: sticky; top: 0; z-index: 10;
  background: rgba(255, 255, 255, 0.82); backdrop-filter: blur(12px); border-bottom: 1px solid var(--line);
}
.secure { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: var(--ok-fg); background: var(--ok-bg); padding: 5px 10px; border-radius: 999px; }
.avatar {
  width: 36px; height: 36px; border-radius: 50%; background: var(--gradient-primary); color: #fff; flex: none;
  display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;
}
.spacer { flex: 1; }
.who { display: flex; flex-direction: column; font-size: 14px; font-weight: 700; line-height: 1.2; }
.who small { font-size: 11px; font-weight: 600; color: var(--muted); }
.content { padding: 28px 32px 56px; max-width: 1280px; width: 100%; margin: 0 auto; }
.page { display: flex; flex-direction: column; gap: 24px; }
@media (max-width: 700px) { .content { padding: 20px 16px; } .topbar { padding: 10px 16px; } }
</style>
