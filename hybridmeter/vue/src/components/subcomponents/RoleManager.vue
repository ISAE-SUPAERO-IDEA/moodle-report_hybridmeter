<template>
    <div id="rolemanager">
        <Message :messages="message.messages" :display="message.display" :params="message.params"/>
        <div class="hybridmeter-field">
            <label>{{ strings.student_archetype }}</label>
            <select v-model="student_archetype" class="custom-select">
                <option v-for="role in roles" :key="role.id" :value="role.archetype">{{ role.archetype }}</option>
            </select>
        </div>
        <div class="hybridmeter-control">
            <button type="submit" class="btn btn-primary" @click="saveStudentArchetype">{{ strings.save_modif }}</button>
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
        const { get, post, getStrings, updateStudentArchetype, displayParam, } = utils();

        const store = useStore();

        const student_archetype = ref(undefined);

        const strings = ref([]);

        const roles = ref([]);

        const loading = ref(false);

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

        store.watch(state => state.student_archetype, data => {
            dispatchCurrentArchetype(data);
            if(loading.value) {
                loading.value = false;
                message.display = displayParam("success");
                store.dispatch("endLoading");
            }
        })

        const load = () => {
            let keys = ["student_archetype", "save_modif", "student_archetype_updated", "error_occured"];
            getStrings(keys).then(output => {
                strings.value = output;
                message.messages.error.message = strings.value.error_occured;
                message.messages.success.message = strings.value.student_archetype_updated;
            });

            let data = [{ task : "roles" }];

            get("moodle_data", data).then(data => roles.value = data).then(dispatchCurrentArchetype(store.state.student_archetype));
        }

        const saveStudentArchetype = () => {
            if(store.state.student_archetype == student_archetype.value) {
                message.display = displayParam("success");
            }
            else {
                let action="additional_config";
                var data = new FormData();
                data.append('action', action);
                data.append('student_archetype', student_archetype.value);
                data.append('debug', store.state.debug);

                if(!loading.value) {
                    loading.value = true;
                    store.dispatch('beginLoading');
                }

                post(`configuration_handler`, data)
                .then(updateStudentArchetype())
                .catch(error => {
                    loading.value = false;
                    store.dispatch('endLoading');
                    message.params = [error.response.status]
                    message.display = displayParam("error");
                });
            }
        }

        return {
            student_archetype,
            strings,
            roles,
            saveStudentArchetype,
            load,
            loading,
            message,
        }
    },
    created() {
        this.load();
    },
    components : { Message },
}
</script>
