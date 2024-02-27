<!--
  - Hybrid Meter
  - Copyright (C) 2020 - 2024  ISAE-SUPAERO
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