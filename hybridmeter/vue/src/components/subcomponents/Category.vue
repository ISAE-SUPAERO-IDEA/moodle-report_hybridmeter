<template>
    <div class="category">
        <i :title="title_category(category)" class="icon fa fa-fw " :class="class_eye_blacklist()" @click="manage_category_blacklist()" ></i>
        <i class="icon fa fa-fw " :class="category_caret()" @click="expanded = !expanded"></i>
        <span style="font-weight: bold">{{category_data.name}}</span>
        <div v-if="expanded">
            <div v-for="category in tree.categories" :key="category.id" class="category_item">
                <category :category_data="category"></category>
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
import { ref } from 'vue'
import { useStore } from 'vuex'
import utils from '../../utils.js'
import { sprintf } from 'sprintf-js'

export default {
    setup(props) {
        const { post, getStrings, updateBlacklist } = utils();
        
        const tree = ref({
            categories : [],
            courses : [],
        })

        const strings = ref([])

        const store = useStore()

        const blacklisted = ref(false)

        const loadStrings = () => {
            let keys = ["blacklist", "whitelist", "x_category", "x_course", "diagnostic_course"];
            return getStrings(keys).then(output => strings.value = output);
        }

        const loadedChildren = ref(false)

        const loadChildren = () => {
            let data = new FormData();
            data.append('task', 'category_children');
            data.append('id', props.category_data.id);

            return post('blacklist_tree_handler', data).then(data => {
                console.log(data)
                console.log("eh oh grossss "+props.category_data.id)
                tree.value = data;
                loadedChildren.value = true
            });
        }

        const blacklistedCategory = (blacklistData, category) => {
            if (blacklistData == undefined) {
                throw new Error('blacklist data in unavailable');
            }
            
            let x = Object.keys(blacklistData.blacklisted_categories).includes(category);
            let y = blacklistData.save_blacklist_categories.includes(category)

            return (x && y)
        }

        const blacklistedCourse = (blacklistData, course) => {
            if (blacklistData == undefined) {
                throw new Error('blacklist data in unavailable');
            }
            
            let x = Object.keys(blacklistData.blacklisted_courses).includes(course);
            let y = blacklistData.save_blacklist_courses.includes(course);

            console.log((x && y));

            return (x && y);
        }

        const updateDisplayedBlacklist = blacklistData => {
            if(blacklistData != undefined) {
                blacklisted.value = blacklistedCategory(blacklistData, props.category_data.id)
                
                let courses = tree.value.courses
                for (let i = 0; i<courses.length; i++) {
                    courses[i].blacklisted = blacklistedCourse(blacklistData, courses[i].id);
                }
            }
        }

        const loadBlacklist = () => {
            loadChildren().then(() => {updateDisplayedBlacklist(store.state.blacklistData); console.log("wsh ?");})
        }

        function manage_element_blacklist(type, element) {
            var value = !element.blacklisted;
            var data = new FormData();
            data.append('task', 'manage_blacklist');
            data.append('id', element.id);
            data.append('type', type);
            data.append('value', value);
            return post('blacklist_tree_handler', data)
            .then(updateBlacklist())
        }

        store.watch(state => state.blacklistData, blacklistData => {
            console.log("yes bébé 🤙 ça marche ? 👊 "+props.category_data.id)
            updateDisplayedBlacklist(blacklistData);
        })


        const manage_course_blacklist = (course) => {
            manage_element_blacklist("courses", course)
        }

        const manage_category_blacklist = () => {
            manage_element_blacklist("categories", props.category_data.id)
        }

        const class_eye_blacklist = () => {
            return (blacklisted.value ? "fa-eye-slash" : "fa-eye")
        }

        const category_caret = () => {
            return (props.expanded ? "fa-caret-down" : "fa-caret-right")
        }

        const title_category = () => {
            let x = blacklisted.value ? strings.value["whitelist"] : strings.value["blacklist"]
            return sprintf(strings.value["x_category"], x)
        }

        const title_course = (course) => {
            let x = course.blacklisted ? strings.value["whitelist"] : strings.value["blacklist"]
            return sprintf(strings.value["x_course"], x)
        }

        return {
            post,
            getStrings,
            updateBlacklist,
            blacklisted,
            tree,
            strings,
            store,
            loadStrings,
            loadedChildren,
            loadChildren,
            loadBlacklist,
            blacklistedCategory,
            blacklistedCourse,
            updateDisplayedBlacklist,
            manage_element_blacklist,
            manage_course_blacklist,
            manage_category_blacklist,
            class_eye_blacklist,
            category_caret,
            title_category,
            title_course,
        }
    },
    props : {
        category_data : {
            required : true
        },
        expanded : {
            default : false,
        },
    },
    created() {
        this.loadStrings();
        this.loadBlacklist();
    },
    name : "Category",
}
</script>
