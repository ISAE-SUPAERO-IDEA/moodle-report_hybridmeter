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
    <div id="periodmanager" class="hybridmeter-component">
        <i>{{strings.measurement_disclaimer}}</i>
        <Message :messages="message.messages" :display="message.display" :params="message.params"/>
        <!-- Beginning date -->
        <div class="hybridmeter-field">
            <label>{{ strings["begin_date"] }}</label>
            <input class="form-control" type="date"  v-model="begin_date"/>
        </div>
        <!-- End date -->
        <div class="hybridmeter-field">
            <label>{{ strings["end_date"] }}</label>
            <input class="form-control" type="date"  v-model="end_date">
        </div>
        <!-- Submit -->
        <div class="hybridmeter-control">
            <button type="submit" :disabled="!are_dates_filled" class="btn btn-primary" @click="save">{{ strings["save_modif"] }}</button>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed } from 'vue'
import { useStore } from 'vuex'
import utils from '../../utils.js'
import Message from '../Message.vue'

export default{
    setup() {
        const { post, updateProgrammedDates, getStrings, timestamp_to_ui, displayParam } = utils()

        const store = useStore()

        const begin_date = ref(undefined)
        const end_date = ref(undefined)
        
        const strings = ref([])

        const loading = ref(false)

        const message = reactive({
            messages : {
                error_network : {
                    message : "",
                    semantic : "error",
                },
                error_begin_date : {
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

        const are_dates_filled = computed(() => {
            return (begin_date.value && end_date.value);
        });

        const ui_to_timestamp = (text, is_end_date = false) => {
            if(is_end_date){
                text = text+' 23:59:59';
            }
            let date = new Date(text);
            return date.getTime() / 1000;
        }

        const save = async () => {
            let dates = store.state.programmedDates;
            let begin_timestamp = ui_to_timestamp(begin_date.value);
            let end_timestamp = ui_to_timestamp(end_date.value);

            if (begin_timestamp>end_timestamp) {
                message.display = displayParam("error_begin_date");
            }
            else if(dates.begin_date == begin_timestamp && dates.end_date == end_timestamp) {
                message.display = displayParam("success");
            }
            else {
                let action="measurement_period";
                var data = new FormData();
                data.append('action', action);
                data.append('begin_date', begin_timestamp);
                data.append('end_date', end_timestamp);
                data.append('debug', store.state.debug);

                if(!loading.value) {
                    loading.value = true;
                    store.dispatch('beginLoading');
                }

                post(`configuration_handler`, data)
                .catch(error => {
                    loading.value = false;
                    store.dispatch('endLoading');
                    message.params = [error.response.status]
                    message.display = displayParam("error_network");
                })
                .then(() => updateProgrammedDates());
            }
        }

        const dispatchDates = (dates) => {
            if(dates != undefined) {
                begin_date.value = timestamp_to_ui(dates.begin_date);
                end_date.value = timestamp_to_ui(dates.end_date);
            }
        }

        const load = async () => {
            let keys = ["begin_date", "end_date", "save_modif", "success_program",
                         "error_occured", "error_begin_after_end", "measurement_disclaimer"];
            getStrings(keys).then(output => {
                strings.value = output;
                message.messages.error_network.message = strings.value.error_occured;
                message.messages.error_begin_date.message = strings.value.error_begin_after_end;
                message.messages.success.message = strings.value.success_program;
            });
            dispatchDates(store.state.programmedDates);
        }

        store.watch(state => state.programmedDates, dates => {
            dispatchDates(dates)
            if(loading.value) {
                loading.value = false;
                store.dispatch('endLoading');
                message.display = displayParam("success");
            }
        })
        
        return {
            message,
            save,
            strings,
            loading,
            begin_date,
            end_date,
            load,
            are_dates_filled,
        }
    },
    created() {
        this.load();
    },
    name : "PeriodManager",
    components : { Message },
}
</script>
