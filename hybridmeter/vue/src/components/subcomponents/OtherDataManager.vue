<!--
  - This file is part of Moodle - http://moodle.org/
  -
  -  Moodle is free software: you can redistribute it and/or modify
  -  it under the terms of the GNU General Public License as published by
  -  the Free Software Foundation, either version 3 of the License, or
  -  (at your option) any later version.
  -
  -  Moodle is distributed in the hope that it will be useful,
  -  but WITHOUT ANY WARRANTY; without even the implied warranty of
  -  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  -  GNU General Public License for more details.
  -
  -  You should have received a copy of the GNU General Public License
  -  along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
  -->

<!--
  - @author Nassim Bennouar, Bruno Ilponse
  - @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  - @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 -->
<template>
    <div id="rolemanager">
        <Message :messages="message.messages" :display="message.display" :params="message.params"/>
        <div class="hybridmeter-field">
            <label>{{ strings.student_archetype }}</label>
            <select v-model="student_archetype" class="custom-select">
                <option v-for="role in roles" :key="role.id" :value="role.archetype">{{ role.archetype }}</option>
            </select>
        </div>
        <div class="hybridmeter-field">
            <label>{{ strings.debug_mode }}</label>
            <input type="checkbox" v-model="debug">
        </div>
        <div class="hybridmeter-control">
            <button type="submit" class="btn btn-primary" @click="saveOtherData">{{ strings.save_modif }}</button>
        </div>
    </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useStore } from 'vuex'
import utils from '../../utils.js'
import Message from '../Message.vue'

export default {
    setup() {
        const { get, post, getStrings, updateOtherData, displayParam, } = utils();

        const store = useStore();

        const student_archetype = ref(undefined);

        const debug = ref(undefined);

        const strings = ref([]);

        const roles = ref([]);

        const message = reactive({
            messages : {
                error : {
                    message : "",
                    semantic : "error",
                },
                success : {
                    message : "",
                    semantic : "success",
                },
            },
            display : undefined,
            params : [],
        });

        const dispatchCurrentArchetype = (archetype) => {
            student_archetype.value = archetype;
        };
        const dispatchCurrentDebug = (d) => {
            debug.value = d;
        };

        store.watch(state => state.student_archetype, data => {
            dispatchCurrentArchetype(data);
        })

        const load = () => {
            let keys = ["student_archetype", "save_modif", "student_archetype_updated", "error_occured", "debug_mode"];
            getStrings(keys).then(output => {
                strings.value = output;
                message.messages.error.message = strings.value.error_occured;
                message.messages.success.message = strings.value.student_archetype_updated;
            });

            let data = [{ task : "roles" }];
            dispatchCurrentDebug(store.state.debug) 

            get("moodle_data", data).then(data => roles.value = data).then(() => {
                dispatchCurrentArchetype(store.state.student_archetype)
            });
        }
        store.watch(state => state.debug, debug => {
            dispatchCurrentDebug(debug ? true : false) 
        })

        const saveOtherData = () => {
            if(store.state.student_archetype == student_archetype.value
            && store.state.debug == debug.value) {
                message.display = displayParam("success");
            }
            else {
                let action="additional_config";
                var data = new FormData();
                data.append('action', action);
                data.append('student_archetype', student_archetype.value);
                data.append('debug', debug.value ? 1 : 0);

                post(`configuration_handler`, data)
                .then(async () => {
                    await updateOtherData();
                    message.display = displayParam("success");
                });
            }
        }

        return {
            student_archetype,
            debug,
            strings,
            roles,
            saveOtherData,
            load,
            message,
        }
    },
    created() {
        this.load();
    },
    components : { Message },
}
</script>
