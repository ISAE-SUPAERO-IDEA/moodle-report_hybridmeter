<template>
    <div id="app">
        <h3 class="main">{{ blacklistTitle }}</h3>
        <!--<BlacklistManager/>-->
        <hr/>
        <h3 class="main">{{ periodTitle }}</h3>
        <!--<PeriodManager/>-->
        <hr/>
    </div>
</template>

<script>
import { get_strings as getStrings } from 'core/str';
import { PLUGIN_FRANKENSTYLE } from '../constants.js';
import { buildStringsArgument } from '../utility.js';
import BlacklistManager from './BlacklistManager.vue';

export default {
    data() {
        return {
            blacklistTitle : "",
            periodTitle : "",
        }
    },
    created(){
        const keys = ["labelblacklist", "labelperiod"];
        const strings_ref = buildStringsArgument(keys, PLUGIN_FRANKENSTYLE);

        getStrings(strings_ref).then(strings => {
            this.blacklistTitle = strings[0];
            this.periodTitle = strings[1];
        });
    },
    components : { BlacklistManager },
    name: "Management",
}
</script>
