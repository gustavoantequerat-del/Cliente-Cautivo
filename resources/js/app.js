import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import PortalLayout from './Layouts/PortalLayout.vue';
import AdminLayout from './Layouts/AdminLayout.vue';

createInertiaApp({
    title: (title) => (title ? `${title} · Portal Cliente` : 'Portal Cliente · Blockfinity'),
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        const page = pages[`./Pages/${name}.vue`];
        if (page.default.layout === undefined) {
            if (name.startsWith('Portal/')) page.default.layout = PortalLayout;
            else if (name.startsWith('Admin/')) page.default.layout = AdminLayout;
        }
        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) }).use(plugin).mount(el);
    },
    progress: { color: '#3B5B8C' },
});
