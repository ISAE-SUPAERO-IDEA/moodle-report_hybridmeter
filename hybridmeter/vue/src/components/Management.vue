<template>
    <div id="app">
        <h3 class="main" @click="lol()">{{ strings["blacklist_title"] }}</h3>
        <BlacklistManager />
        <hr/>
        <h3 class="main">{{ strings["period_title"] }}</h3>
        <PeriodManager/>
        <hr/>
        <h3 class="main">{{ strings["next_schedule_title"] }}</h3>
        <!--<ScheduleManager/>-->
        <hr/>
        <h3 class="main">{{ strings["additional_config_title"] }}</h3>
        <!--<AdditionalManager/>-->
        <hr/>
        <h3 class="main">{{ strings["coeff_value_title"] }}</h3>
        <!--<CoeffsManager/>-->
        <hr/>
        <h3 class="main">{{ strings["treshold_value_title"] }}</h3>
        <!--<TresholdsManager/>-->
        <hr/>
    </div>
</template>

<script>
import { ref } from 'vue'
import PeriodManager from './subcomponents/PeriodManager.vue'
import utils from '../utils.js'
import BlacklistManager from './subcomponents/BlacklistManager.vue'
//import { useStore } from 'vuex'

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
                "coeff_value_title", "treshold_value_title", ];
        this.getStrings(keys).then(strings => this.strings = strings)
    },
    components : { PeriodManager, BlacklistManager, },
    name : "Management",
}
</script>
