/*
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * This file is part of Moodle - http://moodle.org/
 *
 *  Moodle is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Moodle is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
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
