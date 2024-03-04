<!--
  - @author Nassim Bennouar
  - @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  - @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
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