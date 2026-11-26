<script setup>
import { computed, onBeforeUnmount } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Componente_Calculadora_Codigo from '../../Componentes/Componente_Calculadora_Codigo.vue';
import Componente_Reproductor_Video from '../../Componentes/Componente_Reproductor_Video.vue';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';
import Componente_Marca_Blockfinity from '../../Componentes/Componente_Marca_Blockfinity.vue';
import { Construir_Variables_Marca } from '../../Utilidades/Paleta_Marca.js';

const Propiedades = defineProps({ Recurso: Object, Campos: Array, Valores: Object, Opciones: Object, Id_Registro: Number, Proveedores_Video: Object });

// Los campos de archivo empiezan vacíos: se envían solo si se elige uno nuevo
const Valores_Iniciales = { ...Propiedades.Valores };
for (const Campo of Propiedades.Campos) {
    if (Campo.Tipo === 'archivo') {
        Valores_Iniciales[Campo.Nombre] = null;
        Valores_Iniciales[`${Campo.Nombre}_quitar`] = false;
    }
    if (Campo.Tipo === 'unidades') {
        const Estado_Unidades = {};
        for (const Unidad of Propiedades.Opciones.Unidades_Negocio) {
            const Unidad_Guardada = Propiedades.Valores[Campo.Nombre]?.[Unidad.Id];
            Estado_Unidades[Unidad.Id] = { activa: !!Unidad_Guardada?.activa, nota: Unidad_Guardada?.nota ?? '', activa_desde: Unidad_Guardada?.activa_desde ?? '' };
        }
        Valores_Iniciales[Campo.Nombre] = Estado_Unidades;
    }
}
const Formulario = useForm(Valores_Iniciales);
const Es_Edicion = computed(() => !!Propiedades.Id_Registro);
const Tipo_Input_Por_Campo = { email: 'email', enlace: 'url', enlace_drive: 'url', color: 'color', telefono: 'tel', fecha: 'date', fecha_hora: 'datetime-local', contrasena: 'password' };
const Teclado_Por_Campo = { email: 'email', enlace: 'url', enlace_drive: 'url', telefono: 'tel', numerico: 'numeric', entero: 'numeric', decimal: 'decimal' };
const Largo_Maximo_Por_Campo = { texto: 255, nombre_persona: 120, email: 255, enlace: 500, enlace_drive: 500, telefono: 16, numerico: 20, identificador: 80, codigo: 20, entero: 9, codigo_verificacion: 64 };
const Campos_Ancho_Completo = ['texto_largo', 'unidades', 'enlace_video', 'codigo_verificacion'];

// Qué se puede teclear en cada tipo de campo (el Backend vuelve a validar lo mismo)
const Limpiadores_Por_Tipo = {
    telefono: (Valor) => Valor.replace(/[^\d+]/g, '').replace(/(?!^)\+/g, '').slice(0, 16),
    numerico: (Valor) => Valor.replace(/\D/g, '').slice(0, 20),
    entero: (Valor) => Valor.replace(/\D/g, '').slice(0, 9),
    decimal: (Valor, Campo) => {
        const Permite_Negativo = !(Campo.Minimo >= 0);
        const Es_Negativo = Permite_Negativo && Valor.trim().startsWith('-');
        const [Parte_Entera, ...Decimales] = Valor.replace(',', '.').replace(/[^\d.]/g, '').split('.');
        return (Es_Negativo ? '-' : '') + Parte_Entera.slice(0, 9) + (Decimales.length ? '.' + Decimales.join('').slice(0, 2) : '');
    },
    identificador: (Valor) => Valor.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '').replace(/-{2,}/g, '-').slice(0, 80),
    codigo: (Valor) => Valor.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 20),
    nombre_persona: (Valor) => Valor.replace(/[^\p{L}\s'.-]/gu, '').slice(0, 120),
    codigo_verificacion: (Valor) => Valor.toLowerCase().replace(/[^0-9a-f]/g, '').slice(0, 64),
};
function Filtrar_Entrada(Evento, Campo) {
    const Limpiar = Limpiadores_Por_Tipo[Campo.Tipo];
    const Valor_Limpio = Limpiar ? Limpiar(Evento.target.value, Campo) : Evento.target.value;
    Evento.target.value = Valor_Limpio;
    Formulario[Campo.Nombre] = Valor_Limpio;
}
// Instituciones: así se verá su portal con los colores y el logo elegidos
const Variables_Marca_En_Diseno = computed(() => Construir_Variables_Marca(Formulario.color_primario, Formulario.color_secundario));
let Url_Logo_Temporal = null;
const Url_Logo_En_Diseno = computed(() => {
    if (Url_Logo_Temporal) URL.revokeObjectURL(Url_Logo_Temporal);
    Url_Logo_Temporal = Formulario.ruta_logo instanceof File ? URL.createObjectURL(Formulario.ruta_logo) : null;
    if (Url_Logo_Temporal) return Url_Logo_Temporal;
    const Campo_Logo = Propiedades.Campos.find((Campo) => Campo.Nombre === 'ruta_logo');
    return Formulario.ruta_logo_quitar ? null : Campo_Logo?.Archivo_Actual?.Url ?? null;
});
onBeforeUnmount(() => { if (Url_Logo_Temporal) URL.revokeObjectURL(Url_Logo_Temporal); });
// Campos que dependen de otro (ej. el PDF solo si el contenido es "PDF")
const Es_Campo_Visible = (Campo) => !Campo.Visible_Si || Formulario[Campo.Visible_Si[0]] === Campo.Visible_Si[1];
const Texto_Rango = (Campo) => (Campo.Minimo !== undefined && Campo.Maximo !== undefined ? `${Campo.Minimo} – ${Campo.Maximo}` : undefined);

function Guardar_Registro() {
    const Url = Es_Edicion.value ? `/admin/${Propiedades.Recurso.Clave}/${Propiedades.Id_Registro}` : `/admin/${Propiedades.Recurso.Clave}`;
    Formulario.transform((Datos) => {
        const Datos_A_Enviar = { ...Datos };
        for (const Campo of Propiedades.Campos) {
            if (Campo.Tipo === 'si_no') Datos_A_Enviar[Campo.Nombre] = Datos[Campo.Nombre] ? 1 : 0;
            if (Campo.Tipo === 'archivo' && !Datos[Campo.Nombre]) delete Datos_A_Enviar[Campo.Nombre];
            if (Campo.Tipo === 'unidades') {
                Datos_A_Enviar[Campo.Nombre] = Object.fromEntries(Object.entries(Datos[Campo.Nombre])
                    .map(([Id_Unidad, Unidad]) => [Id_Unidad, { ...Unidad, activa: Unidad.activa ? 1 : 0 }]));
            }
        }
        return Datos_A_Enviar;
    }).post(Url, { forceFormData: true, preserveScroll: true });
}

function Eliminar_Registro() {
    if (window.confirm(`¿Eliminar este/a ${Propiedades.Recurso.Singular}? Esta acción no se puede deshacer.`)) {
        router.delete(`/admin/${Propiedades.Recurso.Clave}/${Propiedades.Id_Registro}`);
    }
}

// Vista previa del video: misma lógica que Servicio_Enlaces_Video del Backend
function Construir_Vista_Previa_Video(Enlace) {
    if (!Enlace) return null;
    let Coincidencia = Enlace.match(/drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?(?:export=\w+&)?id=)([A-Za-z0-9_-]{10,})/);
    if (Coincidencia) return { Url_Embebida: `https://drive.google.com/file/d/${Coincidencia[1]}/preview`, Proveedor: 'drive' };
    Coincidencia = Enlace.match(/(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/);
    if (Coincidencia) return { Url_Embebida: `https://www.youtube-nocookie.com/embed/${Coincidencia[1]}`, Proveedor: 'youtube' };
    Coincidencia = Enlace.match(/vimeo\.com\/(?:video\/)?(\d+)(?:\/([A-Za-z0-9]+))?(?:\?.*?h=([A-Za-z0-9]+))?/);
    if (Coincidencia) {
        const Hash = Coincidencia[2] || Coincidencia[3];
        return { Url_Embebida: `https://player.vimeo.com/video/${Coincidencia[1]}${Hash ? `?h=${Hash}` : ''}`, Proveedor: 'vimeo' };
    }
    return false;
}
const Extensiones_Aceptadas = (Campo) => Campo.Extensiones?.split(',').map((Extension) => `.${Extension}`).join(',');
</script>

<template>
    <Head :title="`${Es_Edicion ? 'Editar' : 'Crear'} ${Recurso.Singular}`" />
    <Link :href="`/admin/${Recurso.Clave}`" :class="$style.back">← {{ Recurso.Titulo }}</Link>
    <Componente_Encabezado_Pagina :Icono="Es_Edicion ? 'lapiz' : 'agregar'" :Antetitulo="Recurso.Titulo" :Titulo="`${Es_Edicion ? 'Editar' : 'Nuevo/a'} ${Recurso.Singular}`" />

    <div :class="Recurso.Vista_Previa && $style.withPreview">
    <aside v-if="Recurso.Vista_Previa === 'marca'" class="card" :class="$style.previewCard">
        <span class="eyebrow">Así se verá su portal</span>
        <div :class="$style.mockPortal" :style="Variables_Marca_En_Diseno">
            <div :class="$style.mockSide">
                <div :class="$style.mockBrand">
                    <span v-if="Url_Logo_En_Diseno" :class="$style.mockLogo"><img :src="Url_Logo_En_Diseno" alt="Logo"></span>
                    <Componente_Marca_Blockfinity Compacta />
                </div>
                <span :class="[$style.mockLink, $style.mockActive]" />
                <span :class="$style.mockLink" />
                <span :class="$style.mockLink" />
            </div>
            <div :class="$style.mockMain">
                <div :class="$style.mockHero"><strong>Buenos días 👋</strong><span>{{ Formulario.nombre || 'Institución' }}</span></div>
                <div :class="$style.mockRow"><span :class="$style.mockTile" /><span :class="$style.mockTile" /></div>
                <span :class="$style.mockButton">Botón</span>
            </div>
        </div>
        <span class="muted" :class="$style.previewHint">El logo de Blockfinity siempre aparece al lado.</span>
    </aside>
    <form class="card" :class="$style.form" novalidate @submit.prevent="Guardar_Registro">
        <div v-for="Campo in Campos" v-show="Es_Campo_Visible(Campo)" :key="Campo.Nombre" :class="['field', Campos_Ancho_Completo.includes(Campo.Tipo) && $style.wide]">
            <template v-if="Campo.Tipo === 'si_no'">
                <label class="checkbox"><input v-model="Formulario[Campo.Nombre]" type="checkbox">{{ Campo.Etiqueta }}</label>
            </template>

            <template v-else-if="Campo.Tipo === 'unidades'">
                <span class="label">{{ Campo.Etiqueta }}</span>
                <div :class="$style.units">
                    <div v-for="Unidad in Opciones.Unidades_Negocio" :key="Unidad.Id" :class="$style.unit">
                        <label class="checkbox"><input v-model="Formulario[Campo.Nombre][Unidad.Id].activa" type="checkbox"><span class="mono">{{ Unidad.Codigo }}</span> {{ Unidad.Nombre }}</label>
                        <template v-if="Formulario[Campo.Nombre][Unidad.Id].activa">
                            <input v-model="Formulario[Campo.Nombre][Unidad.Id].nota" class="input" maxlength="255" placeholder="Nota visible (ej. 50 licencias)" :aria-label="`Nota ${Unidad.Codigo}`">
                            <input v-model="Formulario[Campo.Nombre][Unidad.Id].activa_desde" class="input" type="date" :aria-label="`Activa desde ${Unidad.Codigo}`">
                        </template>
                    </div>
                </div>
            </template>

            <template v-else>
                <label :for="Campo.Nombre">{{ Campo.Etiqueta }}<span v-if="Campo.Obligatorio" aria-hidden="true"> *</span></label>

                <select v-if="Campo.Tipo === 'lista' || Campo.Tipo === 'relacion'" :id="Campo.Nombre" v-model="Formulario[Campo.Nombre]" class="select">
                    <option :value="null">{{ Campo.Obligatorio ? 'Seleccione…' : '— Ninguno —' }}</option>
                    <option v-for="Opcion in Opciones[Campo.Nombre]" :key="Opcion.Valor" :value="Opcion.Valor">{{ Opcion.Etiqueta }}</option>
                </select>

                <textarea v-else-if="Campo.Tipo === 'texto_largo'" :id="Campo.Nombre" v-model="Formulario[Campo.Nombre]" class="textarea" maxlength="10000" />

                <template v-else-if="Campo.Tipo === 'archivo'">
                    <div v-if="Campo.Archivo_Actual" class="row">
                        <a :href="Campo.Archivo_Actual.Url" target="_blank" rel="noopener">{{ Campo.Archivo_Actual.Nombre }}</a>
                        <label class="checkbox"><input v-model="Formulario[`${Campo.Nombre}_quitar`]" type="checkbox">Quitar archivo</label>
                    </div>
                    <input :id="Campo.Nombre" type="file" class="input" :accept="Extensiones_Aceptadas(Campo)" @input="Formulario[Campo.Nombre] = $event.target.files[0]">
                    <progress v-if="Formulario.progress" :value="Formulario.progress.percentage" max="100">{{ Formulario.progress.percentage }}%</progress>
                </template>

                <template v-else-if="Campo.Tipo === 'codigo_verificacion'">
                    <input :id="Campo.Nombre" :value="Formulario[Campo.Nombre]" class="input mono" maxlength="64" placeholder="64 caracteres hexadecimales"
                           autocomplete="off" spellcheck="false" @input="Filtrar_Entrada($event, Campo)">
                    <Componente_Calculadora_Codigo Etiqueta="Calcular código desde el archivo original" Ayuda="El archivo no se sube: el código se calcula en este equipo."
                                                   @Codigo_Calculado="(Codigo) => (Formulario[Campo.Nombre] = Codigo)" />
                </template>

                <template v-else-if="Campo.Tipo === 'enlace_video'">
                    <input :id="Campo.Nombre" v-model.trim="Formulario[Campo.Nombre]" class="input" type="url" inputmode="url" maxlength="500" placeholder="https://drive.google.com/file/d/…/view">
                    <span v-if="Construir_Vista_Previa_Video(Formulario[Campo.Nombre]) === false" class="error">No se reconoce como enlace de {{ Object.values(Proveedores_Video).join(', ') }}.</span>
                    <div v-else-if="Construir_Vista_Previa_Video(Formulario[Campo.Nombre])" :class="$style.preview">
                        <span class="help">Vista previa ({{ Proveedores_Video[Construir_Vista_Previa_Video(Formulario[Campo.Nombre]).Proveedor] }}):</span>
                        <Componente_Reproductor_Video v-bind="Construir_Vista_Previa_Video(Formulario[Campo.Nombre])" Titulo="Vista previa" />
                    </div>
                </template>

                <input v-else :id="Campo.Nombre" :value="Formulario[Campo.Nombre]" class="input" :readonly="Campo.Solo_Lectura"
                       :type="Tipo_Input_Por_Campo[Campo.Tipo] ?? 'text'" :inputmode="Teclado_Por_Campo[Campo.Tipo]"
                       :maxlength="Largo_Maximo_Por_Campo[Campo.Tipo]" :placeholder="Texto_Rango(Campo)"
                       :autocomplete="Campo.Tipo === 'contrasena' ? 'new-password' : 'off'"
                       :aria-invalid="Formulario.errors[Campo.Nombre] ? 'true' : undefined" @input="Filtrar_Entrada($event, Campo)">
            </template>

            <span v-if="Campo.Ayuda" class="help">{{ Campo.Ayuda }}</span>
            <span v-if="Formulario.errors[Campo.Nombre]" class="error">{{ Formulario.errors[Campo.Nombre] }}</span>
        </div>

        <div class="row between" :class="[$style.wide, $style.savebar]">
            <button type="submit" class="btn btn-primary" :disabled="Formulario.processing">
                <Componente_Icono :Nombre="Formulario.processing ? 'cargando' : 'visto'" :Tamano="16" :class="Formulario.processing && 'girando'" />
                {{ Formulario.processing ? 'Guardando…' : 'Guardar' }}
            </button>
            <button v-if="Es_Edicion && Recurso.Permite_Eliminar" type="button" class="btn btn-danger" @click="Eliminar_Registro"><Componente_Icono Nombre="basura" :Tamano="16" />Eliminar</button>
        </div>
    </form>
    </div>
</template>

<style module>
.back { align-self: flex-start; font-weight: 700; font-size: 14px; text-decoration: none; padding: 6px 12px; border-radius: 999px; background: var(--surface); border: 1px solid var(--line); }
.savebar { position: sticky; bottom: 0; margin: 0 -24px -24px; padding: 16px 24px; background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(8px); border-top: 1px solid var(--line); border-radius: 0 0 var(--radius-lg) var(--radius-lg); }
.form { display: grid; gap: 20px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr)); max-width: 980px; }
.wide { grid-column: 1 / -1; }
.units { display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr)); }
.unit { border: 1.5px solid var(--line); border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 8px; transition: border-color 0.2s, background 0.2s; }
.unit:has(input[type='checkbox']:checked) { border-color: var(--navy-500); background: var(--blue-50); }
.withPreview { display: flex; flex-wrap: wrap-reverse; gap: 20px; align-items: flex-start; }
.withPreview .form { flex: 999 1 520px; }
.previewCard { flex: 1 1 220px; position: sticky; top: 90px; display: flex; flex-direction: column; align-items: center; gap: 10px; }
.previewHint { font-size: 12px; }
.mockPortal { width: 100%; max-width: 300px; height: 190px; display: flex; border-radius: 14px; overflow: hidden; border: 1px solid var(--line); background: #F2F4F8; }
.mockSide { width: 42%; padding: 10px 8px; display: flex; flex-direction: column; gap: 8px; background: linear-gradient(180deg, var(--navy-950) 0%, var(--navy-900) 100%); }
.mockBrand { display: flex; flex-direction: column; align-items: flex-start; gap: 6px; transform: scale(0.85); transform-origin: left top; }
.mockLogo { display: inline-flex; padding: 3px 6px; border-radius: 6px; background: #fff; }
.mockLogo img { display: block; max-height: 18px; max-width: 70px; object-fit: contain; }
.mockLink { height: 10px; border-radius: 4px; background: rgba(255, 255, 255, 0.15); }
.mockActive { background: var(--navy-700); box-shadow: -3px 0 0 var(--sky-400); }
.mockMain { flex: 1; padding: 10px; display: flex; flex-direction: column; gap: 8px; }
.mockHero { display: flex; flex-direction: column; padding: 8px; border-radius: 8px; color: #fff; font-size: 10px; background: var(--gradient-hero); }
.mockHero span { font-size: 8px; opacity: 0.8; }
.mockRow { display: flex; gap: 6px; }
.mockTile { flex: 1; height: 34px; border-radius: 6px; background: #fff; border: 1px solid var(--blue-100); box-shadow: inset 0 -4px 0 var(--blue-100); }
.mockButton { align-self: flex-start; padding: 4px 10px; border-radius: 6px; font-size: 9px; font-weight: 700; color: #fff; background: var(--gradient-primary); }
.preview { max-width: 520px; display: flex; flex-direction: column; gap: 6px; }
</style>
