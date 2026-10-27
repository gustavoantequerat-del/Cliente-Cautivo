<script setup>
import { computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import FileHasher from '../../../Components/FileHasher.vue';
import VideoPlayer from '../../../Components/VideoPlayer.vue';

const props = defineProps({ resource: Object, fields: Array, values: Object, options: Object, id: Number, providers: Object });

// Los campos de archivo empiezan vacíos; se envían solo si se elige uno nuevo
const initial = { ...props.values };
const unitsState = {};
for (const f of props.fields) {
    if (f.type === 'file') { initial[f.name] = null; initial[`${f.name}_remove`] = false; }
    if (f.type === 'units') {
        for (const u of props.options.units) {
            const cur = props.values[f.name]?.[u.id];
            unitsState[u.id] = { on: !!cur?.on, note: cur?.note ?? '', active_since: cur?.active_since ?? '' };
        }
        initial[f.name] = unitsState;
    }
}
const form = useForm(initial);
const isEdit = computed(() => !!props.id);

function submit() {
    const url = isEdit.value ? `/admin/${props.resource.key}/${props.id}` : `/admin/${props.resource.key}`;
    form.transform((data) => {
        const out = { ...data };
        for (const f of props.fields) {
            if (f.type === 'boolean') out[f.name] = data[f.name] ? 1 : 0;
            if (f.type === 'file' && !data[f.name]) delete out[f.name];
            if (f.type === 'units') {
                out[f.name] = Object.fromEntries(Object.entries(data[f.name]).map(([k, v]) => [k, { ...v, on: v.on ? 1 : 0 }]));
            }
        }
        return out;
    }).post(url, { forceFormData: true, preserveScroll: true });
}

function destroy() {
    if (window.confirm(`¿Eliminar este/a ${props.resource.singular}? Esta acción no se puede deshacer.`)) {
        router.delete(`/admin/${props.resource.key}/${props.id}`);
    }
}

// Vista previa del video: misma lógica que App\Support\VideoEmbed
function embedFrom(url) {
    if (!url) return null;
    let m = url.match(/drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?(?:export=\w+&)?id=)([A-Za-z0-9_-]{10,})/);
    if (m) return { src: `https://drive.google.com/file/d/${m[1]}/preview`, provider: 'gdrive' };
    m = url.match(/(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/);
    if (m) return { src: `https://www.youtube-nocookie.com/embed/${m[1]}`, provider: 'youtube' };
    m = url.match(/vimeo\.com\/(?:video\/)?(\d+)(?:\/([A-Za-z0-9]+))?(?:\?.*?h=([A-Za-z0-9]+))?/);
    if (m) { const h = m[2] || m[3]; return { src: `https://player.vimeo.com/video/${m[1]}${h ? `?h=${h}` : ''}`, provider: 'vimeo' }; }
    return false;
}
</script>

<template>
    <Head :title="`${isEdit ? 'Editar' : 'Crear'} ${resource.singular}`" />
    <Link :href="`/admin/${resource.key}`" :class="$style.back">← {{ resource.label }}</Link>
    <h1>{{ isEdit ? 'Editar' : 'Crear' }} {{ resource.singular }}</h1>

    <form class="card" :class="$style.form" novalidate @submit.prevent="submit">
        <div v-for="f in fields" :key="f.name" :class="['field', ['textarea', 'units', 'video_url', 'hash'].includes(f.type) && $style.wide]">
            <template v-if="f.type === 'boolean'">
                <label class="checkbox"><input v-model="form[f.name]" type="checkbox">{{ f.label }}</label>
            </template>

            <template v-else-if="f.type === 'units'">
                <span class="label">{{ f.label }}</span>
                <div :class="$style.units">
                    <div v-for="u in options.units" :key="u.id" :class="$style.unit">
                        <label class="checkbox"><input v-model="form.units[u.id].on" type="checkbox"><span class="mono">{{ u.code }}</span> {{ u.name }}</label>
                        <template v-if="form.units[u.id].on">
                            <input v-model="form.units[u.id].note" class="input" placeholder="Nota visible (ej. 50 licencias)" :aria-label="`Nota ${u.code}`">
                            <input v-model="form.units[u.id].active_since" class="input" type="date" :aria-label="`Activa desde ${u.code}`">
                        </template>
                    </div>
                </div>
            </template>

            <template v-else>
                <label :for="f.name">{{ f.label }}<span v-if="f.required" aria-hidden="true"> *</span></label>

                <select v-if="f.type === 'select' || f.type === 'belongsTo'" :id="f.name" v-model="form[f.name]" class="select">
                    <option :value="null">{{ f.required ? 'Seleccione…' : '— Ninguno —' }}</option>
                    <option v-for="o in options[f.name]" :key="o.value" :value="o.value">{{ o.label }}</option>
                </select>

                <textarea v-else-if="f.type === 'textarea'" :id="f.name" v-model="form[f.name]" class="textarea" />

                <template v-else-if="f.type === 'file'">
                    <div v-if="f.current" class="row">
                        <a :href="f.current.url" target="_blank" rel="noopener">{{ f.current.name }}</a>
                        <label class="checkbox"><input v-model="form[`${f.name}_remove`]" type="checkbox">Quitar archivo</label>
                    </div>
                    <input :id="f.name" type="file" class="input" :accept="f.accept" @input="form[f.name] = $event.target.files[0]">
                    <progress v-if="form.progress" :value="form.progress.percentage" max="100">{{ form.progress.percentage }}%</progress>
                </template>

                <template v-else-if="f.type === 'hash'">
                    <input :id="f.name" v-model="form[f.name]" class="input mono" maxlength="64" placeholder="64 caracteres hexadecimales">
                    <FileHasher label="Calcular huella desde el archivo original" hint="El archivo no se sube: solo se calcula su huella en este equipo."
                                @hashed="(e) => (form[f.name] = e.hash)" />
                </template>

                <template v-else-if="f.type === 'video_url'">
                    <input :id="f.name" v-model="form[f.name]" class="input" type="url" placeholder="https://drive.google.com/file/d/…/view">
                    <span v-if="embedFrom(form[f.name]) === false" class="error">No se reconoce como enlace de {{ Object.values(providers).join(', ') }}.</span>
                    <div v-else-if="embedFrom(form[f.name])" :class="$style.preview">
                        <span class="help">Vista previa ({{ providers[embedFrom(form[f.name]).provider] }}):</span>
                        <VideoPlayer :src="embedFrom(form[f.name]).src" title="Vista previa" :provider="embedFrom(form[f.name]).provider" />
                    </div>
                </template>

                <input v-else :id="f.name" v-model="form[f.name]" class="input" :readonly="f.readonly"
                       :type="{ email: 'email', url: 'url', number: 'number', decimal: 'number', date: 'date', datetime: 'datetime-local', password: 'password' }[f.type] ?? 'text'"
                       :step="f.type === 'decimal' ? '0.01' : undefined" :min="f.min" :max="f.max"
                       :autocomplete="f.type === 'password' ? 'new-password' : undefined">
            </template>

            <span v-if="f.help" class="help">{{ f.help }}</span>
            <span v-if="form.errors[f.name]" class="error">{{ form.errors[f.name] }}</span>
        </div>

        <div class="row between" :class="$style.wide">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">{{ form.processing ? 'Guardando…' : 'Guardar' }}</button>
            <button v-if="isEdit" type="button" class="btn btn-danger" @click="destroy">Eliminar</button>
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
