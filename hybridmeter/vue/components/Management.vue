<template>
    <div id="app">
        <h3 class="main" @click="lol()">{{ strings["blacklist_title"] }}</h3>
        <!--BlacklistManager wasn't really used, so I directly put the component
        Category into management.vue -->
        <Category :id=0 :expanded=true :root=true />
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
import Category from './subcomponents/Category.vue'
import PeriodManager from './subcomponents/PeriodManager.vue'
import utils from '../utils.js'
import { useStore } from 'vuex'

export default {
    setup() {
        const { getStrings } = utils()

        const strings = ref([])

        return {
            strings,
            get,
            getStrings,
            lol : () => getStrings(["pluginname"]).then(strings => console.log(strings["pluginname"])),            
        }
    },
    beforeMount() {
        await get("configuration_handler").then(config => useStore().dispatch('loadConfig', { config : config }));
    },
    created() {
        const keys = ["blacklist_title", "period_title", "next_schedule_title", "additionnal_config_title", 
                "coeff_value_title", "treshold_value_title", ];
        this.getStrings(keys).then(strings => this.strings = strings)
    },
    components : { Category, PeriodManager, },
    name : "Management",
}
</script>
