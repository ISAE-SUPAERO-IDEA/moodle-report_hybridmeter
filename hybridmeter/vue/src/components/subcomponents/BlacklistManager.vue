<template>
    <div id="blacklistmanager" class="hybridmeter-component">
        <div v-for="category in categories" :key="category.id">
            <Category :category_id="category.id" :category_name="category.name" :strings="strings"></Category>
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