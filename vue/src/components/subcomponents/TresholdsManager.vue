<!--
  - @author Nassim Bennouar
  - @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  - @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 -->
<template>
    <div id="tresholdsmanager" class="hybridmeter-component">
        <table-lite
            class="table"
            :is-loading="table.isLoading"
            :columns="table.columns"
            :rows="table.rows"
            :total="table.totalRecordCount"
            :sortable="table.sortable"
            :page-options="table.pageOptions"
            :is-hide-paging="true"
            @do-search="doSearch"
            @is-finished="table.isLoading = false"
        />
    </div>
</template>

<script>
import TableLite from 'vue3-table-lite';
import utils from '../../utils.js';
import { ref, reactive } from 'vue';

export default {
    setup() {
        const { get, getStrings } = utils();

        let headerClasses = ["hybridmeter-th"]

        const table = reactive({
            isLoading: true,
            columns: [
                {
                    label: "",
                    field: "name",
                    width: "15%",
                    sortable: false,
                    isKey: true,
                    headerClasses : headerClasses,
                },
                {
                    label: "",
                    field: "value",
                    width: "5%",
                    sortable: false,
                    headerClasses : headerClasses,
                },
            ],
            rows: [],
            totalRecordCount: 0,
            pageOptions: [
                {
                    value : 10,
                    text : 10,
                },
                {
                    value : 15,
                    text : 15,
                },
                {
                    value : 20,
                    text : 20,
                },
                {
                    value : 30,
                    text : 30,
                },
                {
                    value : 100,
                    text : 100,
                }
            ],
        });

        const tresholds = ref([]);

        const load = () => {
            let keys = ["treshold", "treshold_value"];
            getStrings(keys)
            .then(strings => {
                table.columns[0].label = strings.treshold;
                table.columns[1].label = strings.treshold_value;
            })

            let data = [{ task : "get_tresholds" }];

            get("configuration_handler", data).then(response => {
                tresholds.value = response.rows;
                table.totalRecordCount = response.count;
                doSearch(0,table.pageOptions[0].value);
            });
        }

        const doSearch = (offset, limit) => {
            table.isLoading = true;
            table.rows = tresholds.value.slice(offset, offset+limit);
        }

        return {
            tresholds,
            table,
            doSearch,
            load,
        }
    },
    created() {
        this.load();
    },
    components : { TableLite, },
    name : "CoeffsManager",
}
</script>