<template>
    <div class="blacklistmanager">
        <div v-for="category in categories" :key="category.id">
            <Category :category_data="category" :expanded="false"></Category>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue'
import utils from '../../utils.js'
import Category from './Category.vue'

export default {
    setup() {
        const { post, updateBlacklist } = utils();

        const categories = ref([])

        const loadCategories = () => {
            let data = new FormData();
            data.append('task', 'category_children');
            data.append('id', 0);

            return post('blacklist_tree_handler', data).then(data => {
                categories.value = data.categories;
            });
        }
        
        return {
            categories,
            loadCategories,
            updateBlacklist,
        }
    },
    created() {
        console.log("hohohooooo")
        this.updateBlacklist()
        this.loadCategories()
    },
    components : { Category },
    name : "BlacklistManager",
}
</script>