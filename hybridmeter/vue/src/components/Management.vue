<template>
    <div id="hybridmeter-app">
        <h3 class="main">{{ strings["blacklist_title"] }}</h3>
        <BlacklistManager />
        <hr/>
        <h3 class="main">{{ strings["period_title"] }}</h3>
        <PeriodManager/>
        <hr/>
        <h3 class="main">{{ strings["next_schedule_title"] }}</h3>
        <ScheduleManager/>
        <hr/>
        <h3 class="main">{{ strings["additional_config_title"] }}</h3>
        <RoleManager/>
        <hr/>
        <h3 class="main">{{ strings["coeff_value_title"] }}</h3>
        <CoeffsManager/>
        <hr/>
        <h3 class="main">{{ strings["treshold_value_title"] }}</h3>
        <TresholdsManager/>
        <hr/>
    </div>
</template>

<script>
import { ref } from 'vue'
import PeriodManager from './subcomponents/PeriodManager.vue'
import utils from '../utils.js'
import BlacklistManager from './subcomponents/BlacklistManager.vue'
import ScheduleManager from './subcomponents/ScheduleManager.vue'
import RoleManager from './subcomponents/RoleManager.vue'
import CoeffsManager from './subcomponents/CoeffsManager.vue'
import TresholdsManager from './subcomponents/TresholdsManager.vue'

export default {
    setup() {
        const { getStrings, loadConfig } = utils()

        const strings = ref([])

        return {
            strings,
            getStrings,
            loadConfig,
        }
    },
    created() {
        this.loadConfig();
        const keys = [ "blacklist_title", "period_title", "next_schedule_title", "additional_config_title", 
                "coeff_value_title", "treshold_value_title", "back_to_plugin" ];
        this.getStrings(keys).then(strings => this.strings = strings)
    },
    components : { 
        PeriodManager,
        BlacklistManager,
        ScheduleManager,
        RoleManager,
        CoeffsManager,
        TresholdsManager,
    },
    name : "Management",
}
</script>
