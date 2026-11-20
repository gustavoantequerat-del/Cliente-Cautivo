<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Componente_Encabezado_Pagina from '../../Componentes/Componente_Encabezado_Pagina.vue';
import Componente_Avatar from '../../Componentes/Componente_Avatar.vue';
import Componente_Icono from '../../Componentes/Componente_Icono.vue';
import Componente_Numero_Animado from '../../Componentes/Componente_Numero_Animado.vue';
import Componente_Estado_Vacio from '../../Componentes/Componente_Estado_Vacio.vue';

const Propiedades = defineProps({ Saldo_Monedas: Number, Objetos_Equipados: Array, Tipos: Object, Items: Array, Ultimas_Monedas: Array });
const Tipo_Activo = ref('camisa');
const Item_En_Prueba = ref(null);
const Item_Procesando = ref(null);

const Items_Del_Tipo = computed(() => Propiedades.Items.filter((Item) => Item.Tipo === Tipo_Activo.value));
const Cantidad_Por_Tipo = (Tipo) => Propiedades.Items.filter((Item) => Item.Tipo === Tipo && Item.Es_Propio).length;

// Lo que lleva puesto + el objeto que está probando (reemplaza al de su misma parte; los extras admiten 2)
function Combinar_Con(Item) {
    const Equipados = Propiedades.Items.filter((Otro) => Otro.Esta_Equipado && Otro.Id !== Item.Id);
    const Del_Mismo_Tipo = Equipados.filter((Otro) => Otro.Tipo === Item.Tipo);
    const Se_Quedan = Item.Tipo === 'extra' ? Del_Mismo_Tipo.slice(-1) : [];
    return [...Equipados.filter((Otro) => Otro.Tipo !== Item.Tipo), ...Se_Quedan, Item];
}
const Objetos_Del_Escenario = computed(() => (Item_En_Prueba.value ? Combinar_Con(Item_En_Prueba.value) : Propiedades.Objetos_Equipados));

function Ejecutar(Accion, Item) {
    Item_Procesando.value = Item.Id;
    router.post(`/avatar/${Item.Id}/${Accion}`, {}, { preserveScroll: true, preserveState: true, onFinish: () => { Item_Procesando.value = null; } });
}
</script>

<template>
    <Head title="Mi avatar" />
    <Componente_Encabezado_Pagina Icono="estrella" Titulo="Mi avatar" Introduccion="Gane monedas con webinars y reportes, y vista a su avatar.">
        <span :class="$style.wallet"><span :class="$style.coin">¢</span><Componente_Numero_Animado :Valor="Saldo_Monedas" /> monedas</span>
    </Componente_Encabezado_Pagina>

    <div :class="$style.layout">
        <aside :class="$style.stageCol">
            <div :class="$style.stage">
                <span :class="$style.floor" />
                <div :class="$style.figure"><Componente_Avatar :Objetos="Objetos_Del_Escenario" /></div>
                <span v-if="Item_En_Prueba" :class="$style.trying">Probando: {{ Item_En_Prueba.Nombre }}</span>
            </div>
            <div class="card stack" :class="$style.earned">
                <h3>¿Cómo gano monedas?</h3>
                <div :class="$style.how">
                    <span class="burbuja burbuja-sm burbuja-roja"><Componente_Icono Nombre="reproducir" :Tamano="16" /></span><span>Completando webinars y píldoras</span>
                    <span class="burbuja burbuja-sm"><Componente_Icono Nombre="grafico" :Tamano="16" /></span><span>Leyendo reportes de Research hasta el final</span>
                </div>
                <ul v-if="Ultimas_Monedas.length" :class="$style.history">
                    <li v-for="(Movimiento, Posicion) in Ultimas_Monedas" :key="Posicion">
                        <span class="lista-texto"><span>{{ Movimiento.Motivo }}</span><span class="lista-detalle">{{ Movimiento.Fecha }}</span></span>
                        <strong :class="Movimiento.Cantidad > 0 ? $style.plus : $style.minus">{{ Movimiento.Cantidad > 0 ? '+' : '' }}{{ Movimiento.Cantidad }}</strong>
                    </li>
                </ul>
            </div>
        </aside>

        <section class="stack" :class="$style.shop">
            <div class="chips" role="tablist" aria-label="Partes del avatar">
                <button v-for="(Nombre_Tipo, Clave_Tipo) in Tipos" :key="Clave_Tipo" type="button" role="tab" class="chip"
                        :aria-selected="Tipo_Activo === Clave_Tipo" :aria-pressed="Tipo_Activo === Clave_Tipo ? 'true' : 'false'" @click="Tipo_Activo = Clave_Tipo">
                    {{ Nombre_Tipo }}<span v-if="Cantidad_Por_Tipo(Clave_Tipo)" :class="$style.owned">{{ Cantidad_Por_Tipo(Clave_Tipo) }}</span>
                </button>
            </div>
            <p v-if="Tipo_Activo === 'extra'" class="muted" :class="$style.hint"><Componente_Icono Nombre="info" :Tamano="14" />Se llevan en la mano: puede usar hasta 2 a la vez.</p>

            <Componente_Estado_Vacio v-if="!Items_Del_Tipo.length" Icono="estrella" Texto="Aún no hay objetos de este tipo en la Forja" />
            <div :class="$style.grid" class="escalonado">
                <article v-for="Item in Items_Del_Tipo" :key="Item.Id" :class="[$style.item, Item.Esta_Equipado && $style.equipped]" class="elevable"
                         @mouseenter="Item_En_Prueba = Item" @mouseleave="Item_En_Prueba = null" @focusin="Item_En_Prueba = Item" @focusout="Item_En_Prueba = null">
                    <div :class="$style.preview"><Componente_Avatar :Objetos="Combinar_Con(Item)" :Recorte="Item.Tipo === 'gorra' ? 'busto' : 'completo'" /></div>
                    <strong :class="$style.itemName">{{ Item.Nombre }}</strong>
                    <span v-if="Item.Detalle" class="lista-detalle" :class="$style.detail">{{ Item.Detalle }}</span>
                    <button v-if="!Item.Es_Propio" type="button" class="btn btn-primary btn-sm" :disabled="Saldo_Monedas < Item.Precio || Item_Procesando === Item.Id" @click="Ejecutar('comprar', Item)">
                        <span :class="$style.coinSmall">¢</span>{{ Item.Precio }}
                    </button>
                    <button v-else type="button" class="btn btn-sm" :class="Item.Esta_Equipado ? 'btn-secondary' : 'btn-primary'" :disabled="Item_Procesando === Item.Id" @click="Ejecutar('equipar', Item)">
                        <Componente_Icono :Nombre="Item.Esta_Equipado ? 'cerrar' : 'visto'" :Tamano="14" />{{ Item.Esta_Equipado ? 'Quitar' : 'Ponerse' }}
                    </button>
                    <span v-if="!Item.Es_Propio && Saldo_Monedas < Item.Precio" :class="$style.missing">Faltan {{ Item.Precio - Saldo_Monedas }}</span>
                </article>
            </div>
        </section>
    </div>
</template>

<style module>
.wallet { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 999px; background: #FCEFD6; color: #8A5A0A; font-weight: 800; }
.coin, .coinSmall {
  display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 800;
  background: linear-gradient(135deg, #F2C94C, #D9A520); color: #6B4A00; box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.15);
}
.coin { width: 24px; height: 24px; font-size: 14px; }
.coinSmall { width: 18px; height: 18px; font-size: 11px; }
.layout { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; }
.stageCol { flex: 1 1 300px; min-width: 0; display: flex; flex-direction: column; gap: 16px; position: sticky; top: 90px; }
.stage {
  position: relative; border-radius: 24px; padding: 24px 24px 16px; display: flex; flex-direction: column; align-items: center;
  background: radial-gradient(circle at 50% 30%, #FFFFFF 0%, #E3E9F2 70%); border: 1px solid var(--line); box-shadow: var(--shadow);
}
.floor { position: absolute; bottom: 34px; width: 55%; height: 18px; border-radius: 50%; background: rgba(10, 22, 40, 0.12); }
.figure { position: relative; width: 220px; height: 308px; animation: aparecer-escala 0.4s var(--ease) both; }
.trying { position: relative; margin-top: 6px; font-size: 12px; font-weight: 700; color: var(--navy-700); background: #fff; padding: 4px 10px; border-radius: 999px; box-shadow: var(--shadow); }
.earned { gap: 12px; }
.how { display: grid; grid-template-columns: auto 1fr; gap: 10px 12px; align-items: center; font-size: 13px; }
.history { list-style: none; margin: 0; padding: 12px 0 0; border-top: 1px solid var(--line); display: flex; flex-direction: column; gap: 10px; font-size: 13px; }
.history li { display: flex; align-items: center; gap: 10px; }
.plus { color: var(--ok-fg); }
.minus { color: var(--danger-fg); }
.shop { flex: 999 1 520px; min-width: 0; }
.owned { font-size: 11px; font-weight: 800; padding: 1px 7px; border-radius: 999px; background: var(--ok-bg); color: var(--ok-fg); }
.hint { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; }
.grid { display: grid; gap: 14px; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
.item {
  display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 12px; border-radius: var(--radius-lg); text-align: center;
  background: var(--surface); border: 1.5px solid var(--line); box-shadow: var(--shadow);
}
.equipped { border-color: #3E9B66; box-shadow: 0 0 0 3px rgba(62, 155, 102, 0.15); }
.preview { width: 100px; height: 140px; border-radius: 14px; background: var(--surface-2); overflow: hidden; }
.itemName { font-size: 14px; line-height: 1.25; }
.detail { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.missing { font-size: 11px; font-weight: 700; color: var(--muted); }
</style>
