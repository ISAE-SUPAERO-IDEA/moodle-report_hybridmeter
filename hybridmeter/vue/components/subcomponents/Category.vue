<template>
    <div class="category">
        <i :title="title_category(category)" class="icon fa fa-fw " :class="class_eye_blacklist(category)" @click="manage_category_blacklist(category)" ></i>
        <i class="icon fa fa-fw " :class="category_caret(category)" @click="expandCategory(category)"></i>
        <span style="font-weight: bold">{{category.name}}</span>
        <div v-if="expanded">
            <div v-for="category in tree.categories" :key="category.id" class="category_item">
                <category :id="category.id" :expanded="category.expanded"></category>
            </div>
            <div v-for="course in tree.courses" :key="course.id" class="category_item" >
                <i :title="title_course(course)" class="icon fa fa-fw " :class="class_eye_blacklist(course)" @click="manage_course_blacklist(course)" ></i>
                <a :title="strings['diagnostic_course']" :href="'tests.php?task=course&id='+course.id"><i class="icon fa fa-fw fa-medkit"></i></a>
                {{course.fullname}}
            </div>
        </div>
    </div>
</template>

<script>
import { ref, watch, toRef } from 'vue'
import { useStore } from 'vuex'
import utils from '../../utils.js'
import { sprintf } from 'sprintf-js'

export default {
    setup(props) {
        const { get, postConfig, post, getStrings, getConfig, updateBlacklist } = utils();
        
        const tree = ref({
            categories : [],
            courses : [],
        })

        const strings = ref([])

        const store = useStore()

        const loadStrings = () => {
            let keys = ["blacklist", "whitelist", "x_category", "x_course", "diagnostic_course"];
            return getStrings(keys).then(output => strings.value = output);
        }

        const loadedChildren = ref(false)

        const loadChildren = () => {
            let data = new FormData();
            data.append('task', 'category_children');
            data.append('id', props.id);

            return post('blacklist_tree_handler', data).then(data => {
                tree.value = data;
                loadedChildren.value = true
            });
        }

        const updateDisplayedBlacklist = blacklistData => {
            if(!loadedChildren.value){
                loadChildren(props.id)
            }

            if (blacklistData.blacklisted_categories) {
                for (var i in tree.value.categories) {
                    if (Object.keys(blacklistData.blacklisted_categories).includes(tree.value.categories[i].id))
                        tree.value.categories[i].blacklisted = true;
                    else
                        tree.value.categories[i].blacklisted = false;
                }
            }
            
            if (blacklistData.blacklisted_courses) {
                for (var i in tree.value.courses) {
                    if (Object.keys(blacklistData.blacklisted_courses).includes(tree.value.courses[i].id))
                        tree.value.courses[i].blacklisted = true;
                    else
                        tree.value.courses[i].blacklisted = false;
                }
            }
        }

        function expandCategory(category) {
            category.expanded = !category.expanded
            //loadChildren(category.id)
        }

        function manage_element_blacklist(type, element) {
            var value = !element.blacklisted;
            console.log("value : "+value)
            var data = new FormData();
            data.append('task', 'manage_blacklist');
            data.append('id', element.id);
            data.append('type', type);
            data.append('value', value);
            return post('blacklist_tree_handler', data)
            .then(updateBlacklist())
        }

        store.watch(state => state.blacklistData, blacklistData => {
            console.log("yes bébé 🤙 ça marche ? 👊 "+props.id)
            if(blacklistData != undefined) {
                updateDisplayedBlacklist(blacklistData);
            }
        })


        const manage_course_blacklist = (course) => {
            manage_element_blacklist("courses", course)
        }

        const manage_category_blacklist = (category) => {
            manage_element_blacklist("categories", category)
        }

        const class_eye_blacklist = (item) => {
            return (item.blacklisted ? "fa-eye-slash" : "fa-eye")
        }

        const category_caret = (category) => {
            return (category.expanded ? "fa-caret-down" : "fa-caret-right")
        }

        const title_category = (category) => {
            let x = category.blacklisted ? strings.value["whitelist"] : strings.value["blacklist"]
            return sprintf(strings.value["x_category"], x)
        }

        const title_course = (course) => {
            let x = course.blacklisted ? strings.value["whitelist"] : strings.value["blacklist"]
            return sprintf(strings.value["x_course"], x)
        }

        return {
            tree,
            strings,
            get,
            postConfig,
            getStrings,
            loadChildren,
            loadStrings,
            manage_course_blacklist,
            manage_category_blacklist,
            class_eye_blacklist,
            category_caret,
            title_category,
            title_course,
            getConfig,
            updateDisplayedBlacklist,
            expandCategory,
        }
    },
    props : {
        category_data : {
            required : true
        },
        global_blacklist : {
            default : false,
        },
        expanded : {
            default : false,
        },
    },
    created() {
        this.loadStrings();
    },
    name : "Category",
}
</script>
