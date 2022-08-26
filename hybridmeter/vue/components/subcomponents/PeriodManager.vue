<template>
    <div id="plage" class="management-module">
        <!-- Beginning date -->
        <div class="form-item row">
            <div class="form-label col-sm-3 text-sm-right">
                <label>{{ strings["begin_date"] }}</label>
            </div>
            <div class="form-setting col-sm-9">
                <div class="form-text defaultsnext">
                    <input type="date"  v-model="begin_date"/>
                </div>
            </div>
        </div>
        <!-- End date -->
        <div class="form-item row">
            <div class="form-label col-sm-3 text-sm-right">
                <label>{{ strings["end_date"] }}</label>
            </div>
            <div class="form-setting col-sm-9">
                <div class="form-text defaultsnext">
                    <input type="date"  v-model="end_date">
                </div>
            </div>
        </div>
        <!-- Submit -->
        <div class="form-item row">
            <div class="form-setting col-sm-9">
            <button type="submit" class="btn btn-primary" @click="save">{{ strings["save_modif"] }}</button>
          </div>
        </div>
    </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useStore } from 'vuex'
import utils from '../../utils.js'

export default{
    setup() {
        const { postConfig, getConfig, updateConfig, getStrings, timestamp_to_ui, ui_to_timestamp } = utils()

        const store = useStore()

        const begin_date = ref(undefined)
        const end_date = ref(undefined)
        
        const strings = ref([])

        const oksaved = ref(false)

        const save = async () => {
            let action="measurement_period";
            var data = new FormData();
            data.append('action', action);
            data.append('begin_date', ui_to_timestamp(begin_date.value));
            data.append('end_date', ui_to_timestamp(end_date.value));
            data.append('debug', store.state.config.debug);
            post(`configuration_handler`, data).updateProgrammedDates();
        }

        const load = async () => {
            let keys = ["begin_date", "end_date", "save_modif"];
            getStrings(keys).then(output => strings.value = output);
        }

        store.watch(state => state.programmedDates, dates => {
            console.log("2pak 🦜 2pak 🦜 ooooooooooh 🥰")
            if(dates != undefined) {
                begin_date.value = timestamp_to_ui(dates.begin_date);
                end_date.value = timestamp_to_ui(dates.end_date);
            }
        })
        
        return {
            postConfig,
            getConfig,
            updateConfig,
            getStrings,
            timestamp_to_ui,
            begin_date,
            end_date,
            strings,
            oksaved,
            save,
            load,
        }
    },
    created() {
        this.load();
    },
    name : "PeriodManager",
}
</script>
