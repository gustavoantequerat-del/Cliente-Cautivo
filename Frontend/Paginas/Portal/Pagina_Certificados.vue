<script setup>
import { Head } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Estado_Vacio from '../../Componentes/Componente_Estado_Vacio.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';

defineProps({ Mis_Certificados: Array, Certificados_Del_Equipo: Array });
</script>

<template>
    <Head title="Mis certificados" />
    <Componente_Encabezado_Pagina Icono="certificado" Titulo="Mis certificados" Introduccion="Sus logros con Blockfinity. Cada uno suma monedas para su avatar." />

    <Componente_Estado_Vacio v-if="!Mis_Certificados.length" Icono="certificado" Texto="Aún no tiene certificados" />
    <div v-else class="grid-3 escalonado">
        <article v-for="Certificado in Mis_Certificados" :key="Certificado.Id" :class="$style.certificate" class="elevable">
            <div :class="$style.ribbon"><Componente_Icono Nombre="certificado" :Tamano="30" /></div>
            <span :class="$style.date">{{ Certificado.Fecha }}</span>
            <h3 :class="$style.title">{{ Certificado.Titulo }}</h3>
            <p v-if="Certificado.Descripcion" :class="$style.description">{{ Certificado.Descripcion }}</p>
            <div class="row between" :class="$style.foot">
                <span v-if="Certificado.Puntaje" class="badge badge-warn"><Componente_Icono Nombre="estrella" :Tamano="12" />+{{ Certificado.Puntaje }} monedas</span>
                <a v-if="Certificado.Tiene_Archivo" :href="`/certificados/${Certificado.Id}/archivo`" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
                    Ver certificado <Componente_Icono Nombre="externo" :Tamano="14" />
                </a>
            </div>
        </article>
    </div>

    <section v-if="Certificados_Del_Equipo" class="stack">
        <h2>Certificados de su equipo</h2>
        <p v-if="!Certificados_Del_Equipo.length" class="vacio">Aún nadie de su equipo recibió certificados.</p>
        <ul v-else class="lista escalonado">
            <li v-for="Certificado in Certificados_Del_Equipo" :key="Certificado.Id">
                <span class="burbuja burbuja-sm burbuja-ambar"><Componente_Icono Nombre="certificado" :Tamano="18" /></span>
                <span class="lista-texto">
                    <strong>{{ Certificado.Titulo }}</strong>
                    <span class="lista-detalle">{{ Certificado.Persona }} · {{ Certificado.Fecha }}</span>
                </span>
                <span class="lista-acciones">
                    <a v-if="Certificado.Tiene_Archivo" :href="`/certificados/${Certificado.Id}/archivo`" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">Ver</a>
                </span>
            </li>
        </ul>
    </section>
</template>

<style module>
.certificate {
  position: relative; overflow: hidden; display: flex; flex-direction: column; gap: 8px; padding: 22px; border-radius: var(--radius-lg);
  background: linear-gradient(160deg, #FFFFFF 0%, var(--blue-50) 100%); border: 1px solid var(--line); box-shadow: var(--shadow);
}
.certificate::before { content: ''; position: absolute; inset: 0 0 auto; height: 5px; background: linear-gradient(90deg, #F2C94C, #D9A520); }
.ribbon {
  width: 56px; height: 56px; border-radius: 18px; display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, #F2C94C, #D9A520); color: #6B4A00; box-shadow: 0 8px 18px rgba(217, 165, 32, 0.35);
}
.date { font-size: 12px; font-weight: 700; color: var(--muted); }
.title { font-size: 17px; line-height: 1.3; }
.description { font-size: 14px; color: var(--text-2); display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
.foot { margin-top: auto; padding-top: 6px; }
</style>
