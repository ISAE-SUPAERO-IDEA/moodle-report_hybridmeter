<!--
  - Hybryd Meter
  - Copyright (C) 2020 - 2024  ISAE-Supaéro
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as published by
  - the Free Software Foundation, either version 3 of the License, or
  - (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
    <div>
        <div class="hybridmeter-message" v-if="show" :class="classname">
            <span>{{ message }}</span>
            <i class="icon fa fa-close" @click="close()"></i>
        </div>
    </div>
</template>

<script>
import { vsprintf } from 'sprintf-js';
import { ref, computed, watch } from 'vue';
export default {
    setup(props) {
        const display = ref(props.display);
        const messages = ref(props.messages);
        const pulsation = ref(false);
        const params = ref([]);

        const message = computed(() => {
            if (display.value != undefined){
                let raw_message = messages.value[display.value.name].message;
                return vsprintf(raw_message, params.value);
            }
            else
                return "";
        });

        const classname = computed(() => {
            if (display.value != undefined){
                let semantic = messages.value[display.value.name].semantic
                let pulse_class = pulsation.value ? "hybridmeter-pulse" : "";
                return "hybridmeter-message-" + semantic + " " + pulse_class;
            }
            else
                return "";
        });

        watch(props, data => {
            display.value = data.display
            messages.value = data.messages
            params.value = data.params
            pulsation.value = true
            setTimeout(() => {
                pulsation.value = false 
            }, 1000)
        });

        const show = computed(() => {
            return display.value != undefined;
        });

        const close = () => {
            display.value = undefined;
        }

        return {
            display,
            messages,
            message,
            classname,
            show,
            close,
            pulsation,
        }
    },
    props : {
        messages : {
            required : true,
        },
        display : {
            required : true,
        },
        params : {
            required : false,
            type : Array,
        }
    }
}
</script>