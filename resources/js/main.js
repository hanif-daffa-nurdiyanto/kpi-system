import { createApp } from 'vue';
import EmployeeStatsOverview from './Components/EmployeeStatsOverview.vue';

// Inisialisasi komponen Vue untuk widget
document.addEventListener('DOMContentLoaded', () => {
    const statsElement = document.getElementById('employee-stats-app');

    if (statsElement) {
        const cards = JSON.parse(statsElement.dataset.cards || '[]');
        const app = createApp(EmployeeStatsOverview, { cards });
        app.mount('#employee-stats-app');
    }
});