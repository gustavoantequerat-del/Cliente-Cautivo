<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Boton_Solicitar from '../../Componentes/Componente_Boton_Solicitar.vue';
import Componente_Estado_Vacio from '../../Componentes/Componente_Estado_Vacio.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

const Propiedades = defineProps({ Productos: Array, Nivel_Alianza: Object, Puede_Solicitar: Boolean });
const Filtro = ref('todos');
const Cantidad_Contratados = computed(() => Propiedades.Productos.filter((Producto) => Producto.Esta_Contratado).length);
const Productos_Visibles = computed(() => Propiedades.Productos.filter((Producto) => Filtro.value === 'todos'
    || (Filtro.value === 'contratados' ? Producto.Esta_Contratado : !Producto.Esta_Contratado)));
const Texto_Nivel = computed(() => {
    const { Nivel_Actual, Nivel_Siguiente, Servicios_Faltantes } = Propiedades.Nivel_Alianza;
    return Nivel_Siguiente
        ? `${Nivel_Actual.Nombre} · ${Servicios_Faltantes} producto${Servicios_Faltantes === 1 ? '' : 's'} más para ${Nivel_Siguiente.Nombre}`
        : `${Nivel_Actual.Nombre} · todos los beneficios activos`;
});
</script>

<template>
    <Head title="Ecosistema Blockfinity" />
    <Componente_Encabezado_Pagina Icono="capas" Titulo="Ecosistema Blockfinity" Introduccion="Las herramientas y servicios de Blockfinity. Los que tiene contratados cuentan para su nivel de alianza." />

    <div :class="$style.bar">
        <div class="chips" role="group" aria-label="Filtrar productos">
            <button type="button" class="chip" :aria-pressed="Filtro === 'todos' ? 'true' : 'false'" @click="Filtro = 'todos'">Todos <span :class="$style.count">{{ Productos.length }}</span></button>
            <button type="button" class="chip" :aria-pressed="Filtro === 'contratados' ? 'true' : 'false'" @click="Filtro = 'contratados'">Contratados <span :class="$style.count">{{ Cantidad_Contratados }}</span></button>
            <button type="button" class="chip" :aria-pressed="Filtro === 'disponibles' ? 'true' : 'false'" @click="Filtro = 'disponibles'">Por descubrir <span :class="$style.count">{{ Productos.length - Cantidad_Contratados }}</span></button>
        </div>
        <Link href="/credencial" :class="$style.level"><Componente_Icono Nombre="estrella" :Tamano="15" />{{ Texto_Nivel }}</Link>
    </div>

    <Componente_Estado_Vacio v-if="!Productos_Visibles.length" Icono="capas" Texto="No hay productos en esta vista" />
    <div v-else :class="$style.grid" class="escalonado">
        <article v-for="Producto in Productos_Visibles" :key="Producto.Id" :class="[$style.card, Producto.Esta_Contratado && $style.hired]">
            <Link :href="`/ecosistema/${Producto.Id}`" :class="$style.media" :aria-label="`Ver ${Producto.Titulo}`">
                <img v-if="Producto.Url_Imagen" :src="Producto.Url_Imagen" :alt="Producto.Titulo" loading="lazy">
                <span v-else :class="$style.placeholder" aria-hidden="true"><Componente_Icono Nombre="capas" :Tamano="56" :Grosor="1" /><span>{{ Producto.Codigo }}</span></span>
                <span v-if="Producto.Esta_Contratado" :class="$style.ribbon"><Componente_Icono Nombre="visto" :Tamano="13" />Contratado</span>
            </Link>
            <div :class="$style.body">
                <Link :href="`/ecosistema/${Producto.Id}`" :class="$style.title">{{ Producto.Titulo }}</Link>
                <p v-if="Producto.Subtitulo" :class="$style.subtitle">{{ Producto.Subtitulo }}</p>
                <div :class="$style.actions">
                    <Componente_Boton_Solicitar :Producto="Producto" :Puede_Solicitar="Puede_Solicitar" />
                    <a v-if="Producto.Enlace_Pagina" :href="Producto.Enlace_Pagina" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">Ver más <Componente_Icono Nombre="externo" :Tamano="15" /></a>
                    <Link v-else :href="`/ecosistema/${Producto.Id}`" class="btn btn-secondary btn-sm">Ver más <Componente_Icono Nombre="flecha" :Tamano="15" /></Link>
                </div>
            </div>
        </article>
    </div>
</template>

<style module>
.bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
.count { font-size: 11px; font-weight: 800; padding: 1px 7px; border-radius: 999px; background: rgba(10, 22, 40, 0.08); }
.level {
  display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 999px; font-size: 13px; font-weight: 700;
  text-decoration: none; background: var(--gradient-primary); color: #fff;
}
.level:hover { color: #fff; }
.grid { display: grid; gap: 20px; grid-template-columns: repeat(auto-fill, minmax(min(100%, 290px), 1fr)); }
.card {
  display: flex; flex-direction: column; overflow: hidden; border-radius: var(--radius-lg); background: var(--surface);
  border: 1px solid var(--line); box-shadow: var(--shadow); transition: translate 0.25s var(--ease), box-shadow 0.25s;
}
.card:hover { translate: 0 -4px; box-shadow: 0 18px 36px rgba(15, 34, 64, 0.16); }
.hired { border-color: var(--ok-fg); box-shadow: 0 0 0 2px var(--ok-bg), var(--shadow); }
.media { position: relative; display: block; aspect-ratio: 16 / 9; overflow: hidden; background: var(--gradient-hero); }
.media img { width: 100%; height: 100%; object-fit: cover; transition: scale 0.4s var(--ease); }
.card:hover .media img { scale: 1.04; }
.placeholder {
  position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
  color: rgba(255, 255, 255, 0.85); font-weight: 800; letter-spacing: 0.12em; font-size: 13px;
}
.ribbon {
  position: absolute; top: 12px; left: 12px; display: inline-flex; align-items: center; gap: 4px; padding: 5px 10px; border-radius: 999px;
  font-size: 12px; font-weight: 800; background: var(--ok-bg); color: var(--ok-fg); box-shadow: 0 4px 12px rgba(10, 22, 40, 0.18);
}
.body { flex: 1; display: flex; flex-direction: column; gap: 8px; padding: 18px; }
.title { font-size: 18px; font-weight: 800; color: var(--navy-950); text-decoration: none; }
.title:hover { color: var(--navy-700); }
.subtitle { font-size: 14px; color: var(--text-2); line-height: 1.5; }
.actions { margin-top: auto; padding-top: 8px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; }
</style>
