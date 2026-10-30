<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';
import Componente_Numero_Animado from '../../Componentes/Componente_Numero_Animado.vue';

defineProps({ Indicadores: Array, Recursos_Por_Grupo: Object });
const Primer_Nombre = computed(() => usePage().props.Usuario_Actual.Nombre.split(' ')[0]);
const Colores_Indicador = ['', 'burbuja-verde', 'burbuja-ambar', 'burbuja-violeta', 'burbuja-roja'];
</script>

<template>
    <Head title="CMS" />
    <section :class="$style.hero">
        <div>
            <span :class="$style.kicker">Consola Blockfinity</span>
            <h1 :class="$style.title">Hola, {{ Primer_Nombre }} 👋</h1>
        </div>
        <div class="row">
            <Link href="/admin/solicitudes-horas" :class="$style.quick"><Componente_Icono Nombre="reloj" :Tamano="16" />Solicitudes</Link>
            <Link href="/admin/entregables/crear" :class="$style.quick"><Componente_Icono Nombre="agregar" :Tamano="16" />Entregable</Link>
            <Link href="/admin/usuarios/crear" :class="$style.quick"><Componente_Icono Nombre="usuario" :Tamano="16" />Usuario</Link>
        </div>
    </section>

    <section :class="$style.stats" class="escalonado">
        <Link v-for="(Indicador, Posicion) in Indicadores" :key="Indicador.Etiqueta" :href="Indicador.Url" :class="$style.stat" class="elevable">
            <span class="burbuja" :class="Colores_Indicador[Posicion]"><Componente_Icono :Nombre="Indicador.Icono" /></span>
            <strong :class="$style.value"><Componente_Numero_Animado :Valor="Indicador.Valor" /></strong>
            <span :class="$style.label">{{ Indicador.Etiqueta }}</span>
        </Link>
    </section>

    <section v-for="(Recursos, Nombre_Grupo) in Recursos_Por_Grupo" :key="Nombre_Grupo" class="stack">
        <h2 :class="$style.group">{{ Nombre_Grupo }}</h2>
        <div :class="$style.resources" class="escalonado">
            <Link v-for="Recurso in Recursos" :key="Recurso.Clave" :href="`/admin/${Recurso.Clave}`" :class="$style.resource" class="elevable">
                <span class="burbuja burbuja-sm"><Componente_Icono :Nombre="Recurso.Icono" :Tamano="18" /></span>
                <span :class="$style.name">{{ Recurso.Titulo }}</span>
                <span class="badge badge-outline">{{ Recurso.Cantidad }}</span>
            </Link>
        </div>
    </section>
</template>

<style module>
.hero {
  display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;
  padding: 28px 32px; border-radius: 24px; color: #fff; background: var(--gradient-hero); box-shadow: 0 18px 40px rgba(15, 34, 64, 0.22);
}
.kicker { font-size: 12px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--side-label); }
.title { color: #fff; font-size: 30px; }
.quick {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 12px; font-size: 13px; font-weight: 700;
  color: #fff; text-decoration: none; background: rgba(255, 255, 255, 0.12); transition: background 0.2s, transform 0.2s var(--ease);
}
.quick:hover { color: #fff; background: rgba(255, 255, 255, 0.22); transform: translateY(-2px); }
.stats { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 190px), 1fr)); }
.stat {
  display: flex; flex-direction: column; gap: 10px; padding: 20px; border-radius: var(--radius-lg); text-decoration: none; color: var(--ink);
  background: var(--surface); border: 1px solid var(--line); box-shadow: var(--shadow);
}
.stat:hover { color: var(--ink); }
.value { font-size: 32px; font-weight: 800; line-height: 1; color: var(--navy-950); }
.label { font-size: 13px; font-weight: 600; color: var(--muted); }
.group { font-size: 13px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--muted); }
.resources { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 230px), 1fr)); }
.resource {
  display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius); text-decoration: none; color: var(--ink);
  background: var(--surface); border: 1px solid var(--line); box-shadow: var(--shadow); font-weight: 700;
}
.resource:hover { color: var(--ink); }
.name { flex: 1; }
</style>
