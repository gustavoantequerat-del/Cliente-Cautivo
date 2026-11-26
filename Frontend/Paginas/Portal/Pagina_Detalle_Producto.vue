<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Componente_Boton_Solicitar from '../../Componentes/Componente_Boton_Solicitar.vue';
import Componente_Reproductor_Video from '../../Componentes/Componente_Reproductor_Video.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

// Con página: se muestra en un iframe con "Solicitar" y "Ver más" (lleva a la landing). Sin página: el video explicativo.
defineProps({ Producto: Object, Puede_Solicitar: Boolean });
</script>

<template>
    <Head :title="Producto.Titulo" />
    <Link href="/ecosistema" :class="$style.back">← Ecosistema Blockfinity</Link>

    <header class="card" :class="[$style.head, Producto.Esta_Contratado && $style.hired]">
        <div :class="$style.headText">
            <div class="row">
                <span class="badge badge-navy">{{ Producto.Codigo }}</span>
                <span v-if="Producto.Esta_Contratado" class="badge badge-ok"><Componente_Icono Nombre="visto" :Tamano="13" />Contratado por su institución</span>
            </div>
            <h1 :class="$style.title">{{ Producto.Titulo }}</h1>
            <p v-if="Producto.Subtitulo" :class="$style.subtitle">{{ Producto.Subtitulo }}</p>
            <p v-if="Producto.Descripcion" :class="$style.description">{{ Producto.Descripcion }}</p>
            <div v-if="Producto.Tipo_Vista === 'pagina'" :class="$style.actions">
                <Componente_Boton_Solicitar :Producto="Producto" :Puede_Solicitar="Puede_Solicitar" />
                <a :href="Producto.Enlace_Pagina" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">
                    Ver más <Componente_Icono Nombre="externo" :Tamano="15" />
                </a>
            </div>
        </div>
        <span v-if="Producto.Url_Imagen" :class="$style.image"><img :src="Producto.Url_Imagen" :alt="Producto.Titulo"></span>
    </header>

    <section v-if="Producto.Tipo_Vista === 'pagina'" :class="$style.site">
        <div :class="$style.browserBar" aria-hidden="true"><span /><span /><span /><em>{{ Producto.Enlace_Pagina }}</em></div>
        <iframe :src="Producto.Enlace_Pagina" :title="`Página de ${Producto.Titulo}`" loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin" sandbox="allow-scripts allow-same-origin allow-popups allow-forms" />
        <p class="muted" :class="$style.note"><Componente_Icono Nombre="info" :Tamano="14" />¿No se ve la página? Ábrala con "Ver más".</p>
    </section>
    <section v-else-if="Producto.Tipo_Vista === 'video'" :class="$style.video">
        <Componente_Reproductor_Video :Url_Embebida="Producto.Url_Video" :Titulo="Producto.Titulo" />
    </section>
    <p v-else class="vacio">Pronto publicaremos más información de este producto.</p>
</template>

<style module>
.back { align-self: flex-start; font-weight: 700; font-size: 14px; text-decoration: none; padding: 6px 12px; border-radius: 999px; background: var(--surface); border: 1px solid var(--line); }
.head { display: flex; flex-wrap: wrap; gap: 24px; align-items: center; }
.hired { border-color: var(--ok-fg); box-shadow: 0 0 0 2px var(--ok-bg), var(--shadow); }
.headText { flex: 999 1 380px; display: flex; flex-direction: column; gap: 10px; min-width: 0; }
.title { font-size: 30px; }
.subtitle { font-size: 17px; font-weight: 600; color: var(--navy-700); }
.description { font-size: 15px; color: var(--text-2); line-height: 1.65; white-space: pre-line; }
.actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding-top: 4px; }
.image { flex: 1 1 260px; max-width: 420px; aspect-ratio: 16 / 9; border-radius: var(--radius); overflow: hidden; }
.image img { width: 100%; height: 100%; object-fit: cover; }
.site { display: flex; flex-direction: column; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--line); box-shadow: var(--shadow); background: var(--surface); }
.browserBar { display: flex; align-items: center; gap: 6px; padding: 10px 14px; background: var(--surface-2); border-bottom: 1px solid var(--line); }
.browserBar span { width: 10px; height: 10px; border-radius: 50%; background: var(--line-2); }
.browserBar em { margin-left: 8px; font-style: normal; font-size: 12px; color: var(--muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.site iframe { width: 100%; height: min(78vh, 900px); border: 0; background: #fff; }
.note { display: inline-flex; align-items: center; gap: 6px; padding: 10px 14px; font-size: 13px; }
.video { max-width: 960px; }
@media (max-width: 700px) { .title { font-size: 24px; } .site iframe { height: 70vh; } }
</style>
