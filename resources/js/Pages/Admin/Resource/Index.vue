<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '../../../Components/Pagination.vue';
import Icon from '../../../Components/Icon.vue';

const props = defineProps({ resource: Object, columns: Array, rows: Object, filter: Object, q: String });
const search = ref(props.q ?? '');
const f = ref(props.filter?.value ?? '');

function apply() {
    const params = {};
    if (search.value) params.q = search.value;
    if (f.value) params.f = f.value;
    router.get(`/admin/${props.resource.key}`, params, { preserveState: true });
}
</script>

<template>
    <Head :title="resource.label" />
    <div class="row between">
        <h1>{{ resource.label }}</h1>
        <Link :href="`/admin/${resource.key}/crear`" class="btn btn-primary"><Icon name="plus" :size="16" />Nuevo/a {{ resource.singular }}</Link>
    </div>
    <form class="row" @submit.prevent="apply">
        <label class="sr-only" for="q">Buscar</label>
        <input id="q" v-model="search" type="search" class="input" :class="$style.search" placeholder="Buscar…">
        <select v-if="filter" v-model="f" class="select" :class="$style.filter" :aria-label="filter.label" @change="apply">
            <option value="">{{ filter.label }}: todos</option>
            <option v-for="o in filter.options" :key="o.value" :value="o.value">{{ o.label }}</option>
        </select>
        <button type="submit" class="btn btn-secondary">Buscar</button>
    </form>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th v-for="c in columns" :key="c.name">{{ c.label }}</th><th><span class="sr-only">Acciones</span></th></tr></thead>
            <tbody>
                <tr v-for="r in rows.data" :key="r.id">
                    <td v-for="c in columns" :key="c.name">{{ r[c.name] }}</td>
                    <td><Link :href="`/admin/${resource.key}/${r.id}/editar`" class="btn btn-secondary btn-sm">Editar</Link></td>
                </tr>
                <tr v-if="!rows.data.length"><td :colspan="columns.length + 1" class="muted">Sin registros.</td></tr>
            </tbody>
        </table>
    </div>
    <Pagination :links="rows.links" />
</template>

<style module>
.search { max-width: 320px; }
.filter { width: auto; max-width: 280px; }
</style>
