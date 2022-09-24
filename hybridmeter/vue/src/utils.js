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
    
    function get(endpointName, params = []) {
        store.dispatch('beginLoading');

        const myaxios = axios.create({ baseURL : constants.AJAX_URL });
        let endpointFile = endpointName + ".php";

        let data = ""
        let separator = "?"

        params.forEach(
            param => {
                let [key, value] = Object.entries(param)[0];
                data += separator + key + "=" + value;
                if(separator == "?")
                    separator = "&"
            }
        )

        return myaxios.get(endpointFile + data).then(store.dispatch('endLoading')).then(response => response.data);
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

    function date_to_ui(date) {
        let ye = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(date);
        let mo = new Intl.DateTimeFormat('en', { month: '2-digit' }).format(date);
        let da = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(date);

        return `${ye}-${mo}-${da}`;
    }

    function timestamp_to_ui(timestamp) {
        // Create a new JavaScript Date object based on the timestamp
        // multiplied by 1000 so that the argument is in milliseconds, not seconds.
        let date = new Date(timestamp * 1000);
        
        return date_to_ui(date)
    }

    function pad(val){
        return (val<10) ? '0' + val : val;
    }

    function timestamp_to_time(timestamp) {
        let temp_date = new Date(timestamp * 1000);
        let hour = temp_date.getHours();
        let minute = temp_date.getMinutes();
        let output = (pad(hour) + ':' + pad(minute));
        return output;
    }

    function displayParam(name) {
        return { name : name }
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

    function updateScheduledTime() {
        get("configuration_handler").then(config => store.dispatch('updateScheduledTimeFromConfig', config))
    }

    function updateProgrammedDates() {
        get("configuration_handler").then(config => store.dispatch('updateProgrammedDatesFromConfig', config))
    }

    function updateStudentArchetype() {
        get("configuration_handler").then(config => store.dispatch('updateStudentArchetype', config))
    }

    return {
        getStrings,
        get,
        post,
        postConfig,
        date_to_ui,
        timestamp_to_ui,
        timestamp_to_time,
        getConfig,
        loadConfig,
        updateBlacklist,
        updateScheduledTime,
        updateProgrammedDates,
        updateStudentArchetype,
        displayParam,
    }
}
