import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import '../css/app.css';

import EmployeeStatsOverview from './Components/EmployeeStatsOverview.vue';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});

document.addEventListener('alpine:initialized', () => {
    const statsElement = document.getElementById('employee-stats-app');

    if (statsElement) {
        try {
            const cards = JSON.parse(statsElement.dataset.cards || '[]');
            console.log('Cards data:', cards);
            const app = createApp(EmployeeStatsOverview, { cards });
            app.mount('#employee-stats-app');
        } catch (error) {
            console.error('Error initializing widget:', error);
        }
    }
});