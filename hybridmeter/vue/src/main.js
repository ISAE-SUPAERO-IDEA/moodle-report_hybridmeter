/**
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 */

import { createApp } from 'vue'
import store from './store.js'
import Management from './components/Management.vue'

export const init = (www_root, ajax_url, plugin_frankenstyle) => {
    var app = createApp(Management);
    const constants = {
        WWW_ROOT : www_root,
        AJAX_URL : ajax_url,
        PLUGIN_FRANKENSTYLE : plugin_frankenstyle,
    }
    app.use(store);
    app.provide('constants', constants);
    app.mount('#hybridmeter-app');
}
