<template>
    <div id="app">
        <h3 class="main">{{ strings["blacklist_title"] }}</h3>
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
import BlacklistManager from './BlacklistManager.vue'
import utils from '../utils.js'

export default {
    setup() {
        const { getStringsVue } = utils();
        return {
            getStringsVue
        }
    },
    data() {
        return {
            strings : []
        }
    },
    created() {
        const keys = ["blacklist_title", "period_title", "coeff_value_title", "treshold_value_title"];
        this.getStringsVue(keys).then(strings => this.strings = strings);
    },
    components : { BlacklistManager },
    name : "Management",
}
</script>
