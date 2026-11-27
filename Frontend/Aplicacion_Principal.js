import './Estilos/Estilos_Globales.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import Plantilla_Portal from './Plantillas/Plantilla_Portal.vue';
import Plantilla_Administracion from './Plantillas/Plantilla_Administracion.vue';

// Cada página se descarga recién cuando se visita (code splitting): la primera carga es más liviana
const Paginas = import.meta.glob('./Paginas/**/*.vue');

// El Backend indica la página por nombre (ej. "Portal/Pagina_Inicio") y aquí se le asigna su plantilla
async function Resolver_Pagina(Nombre_Pagina) {
    const Pagina = await Paginas[`./Paginas/${Nombre_Pagina}.vue`]();
    if (Pagina.default.layout === undefined) {
        if (Nombre_Pagina.startsWith('Portal/')) Pagina.default.layout = Plantilla_Portal;
        else if (Nombre_Pagina.startsWith('Administracion/')) Pagina.default.layout = Plantilla_Administracion;
    }
    return Pagina;
}

createInertiaApp({
    title: (Titulo) => (Titulo ? `${Titulo} · Portal Cliente` : 'Portal Cliente · Blockfinity'),
    resolve: Resolver_Pagina,
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) }).use(plugin).mount(el);
    },
    progress: { color: '#5AA9E6', delay: 150 },
});
