import { createApp } from 'vue'
import store from './store.js'
import Management from './components/Management.vue'

export const init = () => {
    var app = createApp(Management);
    app.use(store);
    app.mount('#app');
}