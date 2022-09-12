import { inject } from 'vue'
import { useStore } from 'vuex'
import { get_strings } from 'core/str'
import axios from 'axios'

export default function utils() {
    const constants = inject('constants');
    const store = useStore();

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

    function getStrings(keys) {
        store.dispatch('beginLoading');

        const strings_ref = buildStringsArgument(keys, constants.PLUGIN_FRANKENSTYLE);

        return get_strings(strings_ref)
        .then(strings => buildStringsObject(keys, strings))
        .then(store.dispatch('endLoading'));
    }
    
    function get(endpointName) {
        store.dispatch('beginLoading');

        const myaxios = axios.create({ baseURL : constants.AJAX_URL });
        let endpointFile = endpointName + ".php";

        return myaxios.get(endpointFile).then(store.dispatch('endLoading')).then(response => response.data);
    }
    
    function post(endpointName, data) {
        store.dispatch('beginLoading');

        const myaxios = axios.create({ baseURL : constants.AJAX_URL });
        let endpointFile = endpointName + ".php";

        return myaxios.post(endpointFile, data)
            .then(store.dispatch('endLoading'))
            .then(response => response.data);
    }

    function postConfig(endpointName, data) {
        store.dispatch('beginLoading');

        const myaxios = axios.create({ baseURL : constants.AJAX_URL });
        let endpointFile = endpointName + ".php";

        return myaxios.post(endpointFile, data)
            .then(loadConfig())
            .then(store.dispatch('endLoading'))
            .then(response => response.data);
    }

    function ui_to_timestamp(text, is_end_date = false) {
        if(is_end_date){
            text = text+' 23:59:59';
        }
        let date = new Date(text);
        return date.getTime() / 1000;
    }

    function timestamp_to_ui(timestamp) {
        // Create a new JavaScript Date object based on the timestamp
        // multiplied by 1000 so that the argument is in milliseconds, not seconds.
        let date = new Date(timestamp * 1000);
        let ye = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(date);
        let mo = new Intl.DateTimeFormat('en', { month: '2-digit' }).format(date);
        let da = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(date);

        // Will display time in 10:30:23 format
        let formattedTime = `${ye}-${mo}-${da}`;

        return formattedTime;
    }

    function getConfig() {
        return store.getters.getConfig;
    }

    function loadConfig() {
        get("configuration_handler").then(config => store.dispatch('loadConfig', config))
    }

    function updateBlacklist() {
        get("configuration_handler").then(config => store.dispatch('updateBlacklistFromConfig', config))
    }

    function updateScheduledData() {
        get("configuration_handler").then(config => store.dispatch('updateScheduledDateFromConfig', config))
    }

    function updateProgrammedDates() {
        get("configuration_handler").then(config => store.dispatch('updateProgrammedDatesFromConfig', config))
    }

    return {
        getStrings,
        get,
        post,
        postConfig,
        ui_to_timestamp,
        timestamp_to_ui,
        getConfig,
        loadConfig,
        updateBlacklist,
        updateScheduledData,
        updateProgrammedDates,
    }
}
