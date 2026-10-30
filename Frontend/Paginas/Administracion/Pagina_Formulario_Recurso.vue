<script setup>
import { computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Componente_Calculadora_Huella from '../../Componentes/Componente_Calculadora_Huella.vue';
import Componente_Reproductor_Video from '../../Componentes/Componente_Reproductor_Video.vue';

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
const Tipo_Input_Por_Campo = { email: 'email', enlace: 'url', entero: 'number', decimal: 'number', fecha: 'date', fecha_hora: 'datetime-local', contrasena: 'password' };
const Campos_Ancho_Completo = ['texto_largo', 'unidades', 'enlace_video', 'huella'];

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
    <h1>{{ Es_Edicion ? 'Editar' : 'Crear' }} {{ Recurso.Singular }}</h1>

    <form class="card" :class="$style.form" novalidate @submit.prevent="Guardar_Registro">
        <div v-for="Campo in Campos" :key="Campo.Nombre" :class="['field', Campos_Ancho_Completo.includes(Campo.Tipo) && $style.wide]">
            <template v-if="Campo.Tipo === 'si_no'">
                <label class="checkbox"><input v-model="Formulario[Campo.Nombre]" type="checkbox">{{ Campo.Etiqueta }}</label>
            </template>

            <template v-else-if="Campo.Tipo === 'unidades'">
                <span class="label">{{ Campo.Etiqueta }}</span>
                <div :class="$style.units">
                    <div v-for="Unidad in Opciones.Unidades_Negocio" :key="Unidad.Id" :class="$style.unit">
                        <label class="checkbox"><input v-model="Formulario[Campo.Nombre][Unidad.Id].activa" type="checkbox"><span class="mono">{{ Unidad.Codigo }}</span> {{ Unidad.Nombre }}</label>
                        <template v-if="Formulario[Campo.Nombre][Unidad.Id].activa">
                            <input v-model="Formulario[Campo.Nombre][Unidad.Id].nota" class="input" placeholder="Nota visible (ej. 50 licencias)" :aria-label="`Nota ${Unidad.Codigo}`">
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

                <textarea v-else-if="Campo.Tipo === 'texto_largo'" :id="Campo.Nombre" v-model="Formulario[Campo.Nombre]" class="textarea" />

                <template v-else-if="Campo.Tipo === 'archivo'">
                    <div v-if="Campo.Archivo_Actual" class="row">
                        <a :href="Campo.Archivo_Actual.Url" target="_blank" rel="noopener">{{ Campo.Archivo_Actual.Nombre }}</a>
                        <label class="checkbox"><input v-model="Formulario[`${Campo.Nombre}_quitar`]" type="checkbox">Quitar archivo</label>
                    </div>
                    <input :id="Campo.Nombre" type="file" class="input" :accept="Extensiones_Aceptadas(Campo)" @input="Formulario[Campo.Nombre] = $event.target.files[0]">
                    <progress v-if="Formulario.progress" :value="Formulario.progress.percentage" max="100">{{ Formulario.progress.percentage }}%</progress>
                </template>

                <template v-else-if="Campo.Tipo === 'huella'">
                    <input :id="Campo.Nombre" v-model="Formulario[Campo.Nombre]" class="input mono" maxlength="64" placeholder="64 caracteres hexadecimales">
                    <Componente_Calculadora_Huella Etiqueta="Calcular huella desde el archivo original" Ayuda="El archivo no se sube: solo se calcula su huella en este equipo."
                                                   @Huella_Calculada="(Huella) => (Formulario[Campo.Nombre] = Huella)" />
                </template>

                <template v-else-if="Campo.Tipo === 'enlace_video'">
                    <input :id="Campo.Nombre" v-model="Formulario[Campo.Nombre]" class="input" type="url" placeholder="https://drive.google.com/file/d/…/view">
                    <span v-if="Construir_Vista_Previa_Video(Formulario[Campo.Nombre]) === false" class="error">No se reconoce como enlace de {{ Object.values(Proveedores_Video).join(', ') }}.</span>
                    <div v-else-if="Construir_Vista_Previa_Video(Formulario[Campo.Nombre])" :class="$style.preview">
                        <span class="help">Vista previa ({{ Proveedores_Video[Construir_Vista_Previa_Video(Formulario[Campo.Nombre]).Proveedor] }}):</span>
                        <Componente_Reproductor_Video v-bind="Construir_Vista_Previa_Video(Formulario[Campo.Nombre])" Titulo="Vista previa" />
                    </div>
                </template>

                <input v-else :id="Campo.Nombre" v-model="Formulario[Campo.Nombre]" class="input" :readonly="Campo.Solo_Lectura"
                       :type="Tipo_Input_Por_Campo[Campo.Tipo] ?? 'text'"
                       :step="Campo.Tipo === 'decimal' ? '0.01' : undefined" :min="Campo.Minimo" :max="Campo.Maximo"
                       :autocomplete="Campo.Tipo === 'contrasena' ? 'new-password' : undefined">
            </template>

            <span v-if="Campo.Ayuda" class="help">{{ Campo.Ayuda }}</span>
            <span v-if="Formulario.errors[Campo.Nombre]" class="error">{{ Formulario.errors[Campo.Nombre] }}</span>
        </div>

        <div class="row between" :class="$style.wide">
            <button type="submit" class="btn btn-primary" :disabled="Formulario.processing">{{ Formulario.processing ? 'Guardando…' : 'Guardar' }}</button>
            <button v-if="Es_Edicion" type="button" class="btn btn-danger" @click="Eliminar_Registro">Eliminar</button>
        </div>
    </form>
</template>

<style module>
.back { font-weight: 700; font-size: 14px; text-decoration: none; }
.form { display: grid; gap: 20px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr)); max-width: 980px; }
.wide { grid-column: 1 / -1; }
.units { display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr)); }
.unit { border: 1px solid var(--line); border-radius: 10px; padding: 12px; display: flex; flex-direction: column; gap: 8px; }
.preview { max-width: 520px; display: flex; flex-direction: column; gap: 6px; }
</style>
