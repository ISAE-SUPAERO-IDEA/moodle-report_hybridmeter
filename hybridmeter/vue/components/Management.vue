<template>
    <div id="app">
        <h3 class="main" @click="lol()">{{ strings["blacklist_title"] }}</h3>
        <BlacklistManager/>
        <hr/>
        <h3 class="main">{{ strings["period_title"] }}</h3>
        <!--<PeriodManager/>-->
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
import BlacklistManager from './BlacklistManager.vue'
import utils from '../utils.js'

export default {
    setup() {
        const { getStrings } = utils()

        const strings = ref([])

        return {
            strings,
            getStrings,
            lol : () => getStrings(["pluginname"]).then(strings => console.log(strings["pluginname"])),            
        }
    },
    created() {
        const keys = ["blacklist_title", "period_title", "coeff_value_title", "treshold_value_title"];
        this.getStrings(keys).then(strings => this.strings = strings)
    },
    components : { BlacklistManager },
    name : "Management",
}
</script>
