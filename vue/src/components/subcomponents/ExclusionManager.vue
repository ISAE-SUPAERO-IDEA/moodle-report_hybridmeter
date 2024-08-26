<!--
 - @author Nassim Bennouar
 - @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 - @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
-->


<template>
    <div id="exclusionsmanager" class="hybridmeter-component">
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
        const { get, getStrings, updateExclusions } = utils();

        const strings = ref([]);
        const categories = ref([]);

        const loadStrings = () => {
            let keys = ["excluded_list", "included_list", "x_category", "x_course", "diagnostic_course"];
            return getStrings(keys).then(output => strings.value = output);
        }

        const loadCategories = () => {
            let data = [
                { task : 'category_children' }, 
                { id : 0 },
            ];

            return get('exclusions_tree_handler', data).then(data => {
                categories.value = data.categories;
            });
        }
        
        return {
            strings,
            categories,
            loadCategories,
            updateExclusions,
            loadStrings,
        }
    },
    created() {
        this.loadStrings()
        this.loadCategories()
        this.updateExclusions()
    },
    components : { Category },
    name : "ExclusionsManager",
}
</script>