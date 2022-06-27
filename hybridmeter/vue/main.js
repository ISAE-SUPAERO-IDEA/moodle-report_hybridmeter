import { createApp } from 'vue';
import Management from './components/Management.vue';

export const init = () => {
    var app = createApp(Management);
    app.mount('#app');
};