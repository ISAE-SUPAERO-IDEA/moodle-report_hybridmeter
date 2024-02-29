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
    <div id="blacklistmanager" class="hybridmeter-component">
        <div v-for="category in categories" :key="category.id">
            <Category parent_id="0" :category_id="category.id" :category_name="category.name" :strings="strings"></Category>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue'
import utils from '../../utils.js'
import Category from './Category.vue'

export default {
    setup() {
        const { get, getStrings, updateBlacklist } = utils();

        const strings = ref([]);
        const categories = ref([]);

        const loadStrings = () => {
            let keys = ["blacklist", "whitelist", "x_category", "x_course", "diagnostic_course"];
            return getStrings(keys).then(output => strings.value = output);
        }

        const loadCategories = () => {
            let data = [
                { task : 'category_children' }, 
                { id : 0 },
            ];

            return get('blacklist_tree_handler', data).then(data => {
                categories.value = data.categories;
            });
        }
        
        return {
            strings,
            categories,
            loadCategories,
            updateBlacklist,
            loadStrings,
        }
    },
    created() {
        this.loadStrings()
        this.loadCategories()
        this.updateBlacklist()
    },
    components : { Category },
    name : "BlacklistManager",
}
</script>