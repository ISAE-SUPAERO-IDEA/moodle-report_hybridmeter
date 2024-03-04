/**
* @author Nassim Bennouar
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
* @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
*/
<template>
    <div id="schedulemanager" class="hybridmeter-component">
        <Message :messages="message.messages" :display="message.display" :params="message.params"/>
        <div class="hybridmeter-control">
            <button class="btn btn-outline-primary" @click="set_tomorrow_night()">{{ strings.tonight }}</button>
            <button class="btn btn-outline-primary" @click="set_saturday_night()">{{ strings.this_weekend }}</button>
        </div>
        <div class="hybridmeter-field">
            <label>
                {{ strings.scheduled_date }}
            </label>
            <input class="form-control" type="date" v-model="scheduled_date" />
        </div>
        <div class="hybridmeter-field">
            <label>
                {{ strings.scheduled_time }}
            </label>
            <input class="form-control" type="time" v-model="scheduled_time" />
        </div>
        <div class="hybridmeter-control">
            <button class="btn btn-primary" :disabled="!is_datetime_filled" type="submit" @click="schedule">{{ strings.schedule_submit }}</button>
            <button class="btn btn-secondary" :hidden="!scheduled" type="submit" @click="unschedule">{{ strings.unschedule_submit }}</button>
        </div>
    </div>
</template>

<script>
import { ref, computed, reactive } from 'vue'
import { useStore } from 'vuex'
import utils from '../../utils.js'
import Message from '../Message.vue'

export default{
    setup() {
        const { post, updateScheduledTime, getStrings, timestamp_to_ui, timestamp_to_time, date_to_ui, displayParam } = utils()

        const store = useStore()

        const scheduled = ref(false)

        let scheduled_timestamp = undefined

        const scheduled_date = ref(undefined)
        const scheduled_time = ref(undefined)
        
        const strings = ref([])

        const loading = ref(false)

        const message = reactive({
            messages : {
                error_network : {
                    message : "",
                    semantic : "error",
                },
                error_past : {
                    message : "",
                    semantic : "error",  
                },
                success_schedule : {
                    message : "",
                    semantic : "success",
                },
                success_unschedule : {
                    message : "",
                    semantic : "success",
                },
            },
            display : undefined,
            params : [],
        });

        const is_datetime_filled = computed(() => {
            return (scheduled_date.value && scheduled_time.value)
        })

        const set_tomorrow_night = () => {
            let today = new Date();
            let tomorrow = new Date();
            tomorrow.setDate(today.getDate() + 1);
            scheduled_date.value = date_to_ui(tomorrow);
            scheduled_time.value = "02:00";
        }

        const set_saturday_night = () => {
            let today = new Date();
            let delta = 6 - today.getDay();
            let saturday = new Date();
            saturday.setDate(today.getDate() + delta);

            scheduled_date.value = date_to_ui(saturday);
            scheduled_time.value = "02:00";
        }

        const ui_to_timestamp = () => {
            let date = new Date(scheduled_date.value + " " + scheduled_time.value);
            return date.getTime() / 1000;
        }

        const schedule = async () => {
            let scheduledTime = store.state.scheduledTime;
            let ui_timestamp = ui_to_timestamp();
            let now_timestamp = Math.floor(Date.now() / 1000);
            if ( now_timestamp > ui_timestamp ) {
                message.display = displayParam("error_past");
            }
            else if ( scheduledTime != undefined && scheduledTime.scheduled && scheduledTime.scheduled_timestamp == ui_timestamp) {
                message.display = displayParam("success_schedule");
            }
            else {
                let action="schedule";
                var data = new FormData();
                loading.value = true;
                data.append('action', action);
                data.append('scheduled_timestamp', ui_timestamp);
                data.append('debug', store.state.debug);

                if(!loading.value) {
                    loading.value = true;
                    store.dispatch('beginLoading');
                }

                post(`configuration_handler`, data)
                .then(() => updateScheduledTime())
                .catch(error => {
                    loading.value = false;
                    store.dispatch('endLoading');
                    message.params = [error.response.status]
                    message.display = displayParam("error_network");
                });
            }
        }

        const unschedule = async () => {
            let scheduledTime = store.state.scheduledTime;
            if ( scheduledTime != undefined && !scheduledTime.scheduled) {
                message.display = displayParam("success_unschedule");
            } 
            else{
                let action="unschedule";
                var data = new FormData();
                data.append('action', action);
                data.append('debug', store.state.debug);

                if(!loading.value) {
                    loading.value = true;
                    store.dispatch('beginLoading');
                }

                post(`configuration_handler`, data).then(updateScheduledTime());
            }
        }

        const dispatchScheduledTime = (data) => {
            if(data != undefined) {
                scheduled.value = data.scheduled;

                if(data.scheduled) {
                    scheduled_timestamp = data.scheduled_timestamp;
                    scheduled_time.value = timestamp_to_time(scheduled_timestamp);
                    scheduled_date.value = timestamp_to_ui(scheduled_timestamp);
                } else {
                    scheduled_time.value = undefined;
                    scheduled_date.value = undefined;
                    scheduled_timestamp = undefined;
                }
                return true;
            }
            return false;
        }

        const load = async () => {
            let keys = ["scheduled_date", "scheduled_time", "tonight", "this_weekend", 
                    "schedule_submit", "unschedule_submit", "error_occured", 
                    "success_schedule", "success_unschedule", "error_past_schedule"];
            getStrings(keys).then(output => {
                strings.value = output;
                message.messages.error_network.message = strings.value.error_occured;
                message.messages.error_past.message = strings.value.error_past_schedule;
                message.messages.success_schedule.message = strings.value.success_schedule;
                message.messages.success_unschedule.message = strings.value.success_unschedule;
            });
            dispatchScheduledTime(store.state.scheduledTime);
        }

        store.watch(state => state.scheduledTime, data => {
            dispatchScheduledTime(data);
            if(loading.value) {
                loading.value = false;
                store.dispatch('endLoading');
                let success = (data.scheduled) ? "success_schedule" : "success_unschedule"
                message.display = displayParam(success);
            }
        })
        
        return {
            set_tomorrow_night,
            set_saturday_night,
            scheduled,
            scheduled_date,
            scheduled_time,
            strings,
            load,
            loading,
            is_datetime_filled,
            schedule,
            unschedule,
            message,
        }
    },
    created() {
        this.load();
    },
    components : { Message },
    name : "ScheduleManager",
}
</script>
