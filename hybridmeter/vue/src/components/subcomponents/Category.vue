<template>
    <div class="hybrid-category">
        <i :title="title_category" class="icon fa fa-fw " :class="class_eye_blacklist" @click="manage_category_blacklist()" ></i>
        <i class="icon fa fa-fw " :class="category_caret" @click="expanded = !expanded"></i>
        <span style="font-weight: bold">{{category_name}}</span>
        <div v-if="expanded">
            <div v-for="category in tree.categories" :key="category.id">
                <category :category_id="category.id" :category_name="category.name" :strings="strings"></category>
            </div>
            <div v-for="course in tree.courses" :key="course.id" class="hybrid-course" >
                <i :title="title_course(course)" class="icon fa fa-fw " :class="course_class_eye_blacklist(course)" @click="manage_course_blacklist(course)" ></i>
                <a :title="strings['diagnostic_course']" :href="'tests.php?task=course&id='+course.id"><i class="icon fa fa-fw fa-medkit"></i></a>
                {{course.fullname}}
            </div>
        </div>
    </div>
</template>

<script>
import { ref, watch, computed } from 'vue'
import { useStore } from 'vuex'
import utils from '../../utils.js'
import { sprintf } from 'sprintf-js'

export default {
    setup(props) {
        const { get, post, updateBlacklist } = utils();
        
        const strings = ref(props.strings);

        const tree = ref({
            categories : [],
            courses : [],
        })

        const expanded = ref(false)

        const store = useStore()

        const blacklisted = ref(false)

        const loadedChildren = ref(false)

        const loading = ref(false)

        watch(props, data => {
            strings.value = data.strings;
        });

        const loadChildren = () => {
            let data = [
                { task : 'category_children' }, 
                { id : props.category_id },
            ];

            return get('blacklist_tree_handler', data).then(data => {
                tree.value = data;
                loadedChildren.value = true
            });
        }

        const isBlacklistedCategory = (blacklistData, category) => {
            if (blacklistData == undefined) {
                throw new Error('blacklist data in unavailable');
            }
            
            return (Object.keys(blacklistData.blacklisted_categories).includes(category))
        }

        const isBlacklistedCourse = (blacklistData, course) => {
            if (blacklistData == undefined) {
                throw new Error('blacklist data in unavailable');
            }
            
            return (Object.keys(blacklistData.blacklisted_courses).includes(course));
        }

        const updateDisplayedBlacklist = blacklistData => {
            if(blacklistData != undefined) {
                blacklisted.value = isBlacklistedCategory(blacklistData, props.category_id)
                
                let courses = tree.value.courses
                for (let i = 0; i<courses.length; i++) {
                    courses[i].blacklisted = isBlacklistedCourse(blacklistData, courses[i].id);
                }
            }
        }

        const loadBlacklist = () => {
            loadChildren().then(updateDisplayedBlacklist(store.state.blacklistData))
        }

        function manage_element_blacklist(type, value, id) {
            if(!loading.value) {
                loading.value = true;
                store.dispatch('beginLoading');
            }

            var data = new FormData();
            data.append('task', 'manage_blacklist');
            data.append('id', id);
            data.append('type', type);
            data.append('value', value);
            return post('blacklist_tree_handler', data)
            .then(updateBlacklist())
        }

        const manage_course_blacklist = (course) => {
            manage_element_blacklist("courses", !course.blacklisted, course.id)
        }

        store.watch(state => state.blacklistData, blacklistData => {
            updateDisplayedBlacklist(blacklistData);
            if(loading.value){
                loading.value = false;
                store.dispatch("endLoading");
            }
        })

        const manage_category_blacklist = () => {
            manage_element_blacklist("categories", !blacklisted.value, props.category_id)
        }

        const class_eye_blacklist = computed(() => {
            return (blacklisted.value ? "fa-eye-slash" : "fa-eye")
        });

        const course_class_eye_blacklist = (course) => {
            return (course.blacklisted ? "fa-eye-slash" : "fa-eye")
        };

        const category_caret = computed(() => {
            return (expanded.value ? "fa-caret-down" : "fa-caret-right")
        })

        const title_category = computed(() => {
            let x = blacklisted.value ? strings.value["whitelist"] : strings.value["blacklist"]
            return sprintf(strings.value["x_category"], x)
        })

        const title_course = (course) => {
            let x = course.blacklisted ? strings.value["whitelist"] : strings.value["blacklist"]
            return sprintf(strings.value["x_course"], x)
        }

        return {
            loading,
            strings,
            tree,
            expanded,
            blacklisted,
            loadedChildren,
            loadChildren,
            loadBlacklist,
            manage_course_blacklist,
            manage_category_blacklist,
            class_eye_blacklist,
            course_class_eye_blacklist,
            category_caret,
            title_category,
            title_course,
        }
    },
    props : {
        category_id : {
            required : true,
        },
        category_name : {
            required : true,
            type : String,
        },
        strings : {
            required : true,
        }
    },
    created() {
        this.loadBlacklist();
    },
    name : "Category",
}
</script>
