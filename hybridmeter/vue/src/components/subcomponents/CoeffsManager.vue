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

<!--
 - @author Nassim Bennouar
 - @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 - @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
-->

<template>
    <div id="coeffsmanager" class="hybridmeter-component">
        <table-lite
            :is-loading="table.isLoading"
            :columns="table.columns"
            :rows="table.rows"
            :total="table.totalRecordCount"
            :sortable="table.sortable"
            :page-options="table.pageOptions"
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
        let headerClasses = ["hybridmeter-th"];
        
        const { get, getStrings } = utils();
        const table = reactive({
            isLoading: true,
            columns: [
                {
                    label: "",
                    field: "name",
                    width: "10%",
                    sortable: true,
                    isKey: true,
                    headerClasses: headerClasses,
                },
                {
                    label: "",
                    field: "usage_coeff",
                    width: "5%",
                    sortable: true,
                    headerClasses: headerClasses,
                },
                {
                    label: "",
                    field: "digitalisation_coeff",
                    width: "5%",
                    sortable: true,
                    headerClasses: headerClasses,
                },
            ],
            rows: [],
            totalRecordCount: 0,
            sortable: {
                order: "name",
                sort: "asc",
            },
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

        const coeffs = ref([]);

        const compare_by_name = (a,b) => {
            if (a.name < b.name)
                return -1;
            else if (a.name > b.name)
                return 1;
            else
                return 0;
        }

        const compare_by_name_desc = (a,b) => {
            return (-compare_by_name(a,b));
        }

        const compare_by_usage = (a,b) => {
            if (a.usage_coeff < b.usage_coeff)
                return -1;
            else if (a.usage_coeff > b.usage_coeff)
                return 1;
            else
                return 0;
        }

        const compare_by_usage_desc = (a,b) => {
            return -(compare_by_usage(a,b));
        }

        const compare_by_digitalisation = (a,b) => {
            if (a.digitalisation_coeff < b.digitalisation_coeff)
                return -1;
            else if (a.digitalisation_coeff > b.digitalisation_coeff)
                return 1;
            else
                return 0;
        }

        const compare_by_digitalisation_desc = (a,b) => {
            return -(compare_by_digitalisation(a,b));
        }
        
        const getRows = (offset, limit, order, sort) => {
            let func;
            switch (order) {
                case "name" :
                    func = (sort == "asc") ? compare_by_name : compare_by_name_desc;
                    break;
                case "usage_coeff" :
                    func = (sort == "asc") ? compare_by_usage : compare_by_usage_desc;
                    break;
                case "digitalisation_coeff" :
                    func = (sort == "asc") ? compare_by_digitalisation : compare_by_digitalisation_desc;
                    break;
            }

            let ordered_coeffs = coeffs.value.sort(func);
            
            return ordered_coeffs.slice(offset, offset+limit);
        }

        const load = () => {
            let keys = ["module_name", "usage_coeff", "digitalisation_coeff"];
            getStrings(keys)
            .then(strings => {
                table.columns[0].label = strings.module_name;
                table.columns[1].label = strings.usage_coeff;
                table.columns[2].label = strings.digitalisation_coeff;
            })

            let data = [{ task : "get_all_coeffs" }];

            get("configuration_handler", data).then(response => {
                coeffs.value = response.rows;
                table.totalRecordCount = response.count;
                doSearch(0,table.pageOptions[0].value,table.sortable.order,table.sortable.sort)
            });
        }

        const doSearch = (offset, limit, order, sort) => {
            table.isLoading = true;
            table.rows = getRows(offset, limit, order, sort);
            table.sortable.order = order;
            table.sortable.sort = sort;
        }

        return {
            coeffs,
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