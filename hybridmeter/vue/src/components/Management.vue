<!--
  - This file is part of Moodle - http://moodle.org/
  -
  - Moodle is free software: you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation, either version 3 of the License, or
  - (at your option) any later version.
  -
  - Moodle is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
  -->

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
        <OtherDataManager/>
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
import OtherDataManager from './subcomponents/OtherDataManager.vue'
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
        OtherDataManager,
        CoeffsManager,
        TresholdsManager,
    },
    name : "Management",
}
</script>
