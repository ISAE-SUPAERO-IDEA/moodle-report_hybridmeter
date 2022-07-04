import { inject } from 'vue'
import { useStore } from 'vuex'
import { get_strings as getStrings } from 'core/str'
import axios from 'axios';

export default function utils() {
    const constants = inject('constants');

    function buildStringsArgument(keys, component) {
        let output = [];
        for(let i = 0; i < keys.length; i++) {
            output.push({
                key: keys[i],
                component: component
            })
        }
        return output;
    }
    
    function buildStringsObject(keys, strings) {
        let output = new Object();
        for(let i = 0; i < keys.length; i++) {
            output[keys[i]] = strings[i];
        }
        return output;
    }

    function getStringsVue(keys) {
        useStore().dispatch('beginLoading');

        const strings_ref = buildStringsArgument(keys, constants.PLUGIN_FRANKENSTYLE);

        return getStrings(strings_ref)
        .then(strings => buildStringsObject(keys, strings))
        .then(useStore().dispatch('endLoading'));
    }
    
    function get(endpointName) {
        useStore().dispatch('beginLoading');

        const myaxios = axios.create({ baseURL : constants.AJAX_URL });
        let endpointFile = endpointName + ".php";

        return myaxios.get(endpointFile).then(useStore().dispatch('endLoading')).then(response => response.data);
    }
    
    function post(endpointName, data) {
        useStore().dispatch('beginLoading');

        const myaxios = axios.create({ baseURL : constants.AJAX_URL });
        let endpointFile = endpointName + ".php";

        return myaxios.post(endpointFile, data).then(useStore().dispatch('endLoading')).then(response => response.data);
    }

    return {
        getStringsVue,
        get,
        post,
    }
}
