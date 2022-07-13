import { createApp } from 'vue'
import store from './store.js'
import Management from './components/Management.vue'
import './assets/style/management.css';
import '@fortawesome/fontawesome-free/js/fontawesome'

export const init = (www_root, ajax_url, plugin_frankenstyle) => {
    var app = createApp(Management);
    const constants = {
        WWW_ROOT : www_root,
        AJAX_URL : ajax_url,
        PLUGIN_FRANKENSTYLE : plugin_frankenstyle,
    }
    app.use(store);
    app.provide('constants', constants);
    app.mount('#app');
}
