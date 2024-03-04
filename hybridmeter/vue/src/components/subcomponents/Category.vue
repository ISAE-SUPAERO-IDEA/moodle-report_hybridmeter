<!--
 - @author Nassim Bennouar
 - @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 - @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
-->

<template>
    <div class="hybrid-category">
        <i :title="title_category" class="icon fa fa-fw " :class="class_eye_blacklist" @click="manage_category_blacklist()" ></i>
        <i v-if="loadedChildren && hasChildren" class="icon fa fa-fw " :class="category_caret" @click="expanded = !expanded"></i>
        <i v-else-if="!loadedChildren" class="icon fa fa-fw " :class="category_caret"></i>
        <i v-else class="icon fa"></i>
        <span style="font-weight: bold">{{category_id}} {{category_name}}</span>
        <div v-if="expanded">
            <div v-for="category in tree.categories" :key="category.id">
                <category :parent_id="category_id" :category_id="category.id" :category_name="category.name" :strings="strings"></category>
            </div>
            <div v-for="course in tree.courses" :key="course.id" class="hybrid-course" >
                <i :title="title_course(course)" class="icon fa fa-fw " :class="course_class_eye_blacklist(course)" @click="manage_course_blacklist(course)" ></i>
                <a :title="strings['diagnostic_course']" :href="'tests.php?task=course&id='+course.id"><i class="icon fa fa-fw fa-medkit"></i></a>
                {{course.id}} {{course.fullname}}
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

        const category_id = ref(props.category_id);

        const expanded = ref(false)

        const store = useStore()

        const blacklisted = ref(false)

        const loadedChildren = ref(false)


        watch(props, data => {
            strings.value = data.strings;
        });

        const hasChildren = computed(() => {
            return (tree.value.courses.length + tree.value.categories.length) > 0
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
            
            if (Object.keys(blacklistData.blacklisted_categories).includes(category)) {
                return blacklistData.blacklisted_categories[category];
            }
            else if (props.parent_id == 0) {
                return false;
            }
            else {
                return isBlacklistedCategory(blacklistData, props.parent_id);
            }
        }

        const isBlacklistedCourse = (blacklistData, course) => {
            if (blacklistData == undefined) {
                throw new Error('blacklist data in unavailable');
            }

            if (Object.keys(blacklistData.blacklisted_courses).includes(course)) {
                return blacklistData.blacklisted_courses[course];
            }
            else {
                return isBlacklistedCategory(blacklistData, props.category_id);
            }
        }

        const updateDisplayedBlacklist = blacklistData => {
            if(blacklistData != undefined) {
                let is_blacklisted_category = isBlacklistedCategory(blacklistData, props.category_id);
                blacklisted.value = is_blacklisted_category;
                
                let courses = tree.value.courses
                for (let i = 0; i<courses.length; i++) {
                    courses[i].blacklisted = isBlacklistedCourse(blacklistData, courses[i].id);
                }
            }
        }

        const loadBlacklist = () => {
            loadChildren().then(() => {
                
                updateDisplayedBlacklist(store.state.blacklistData)
            })
        }

        function manage_element_blacklist(type, value, id) {
            var data = new FormData();
            data.append('task', 'manage_blacklist');
            data.append('id', id);
            data.append('type', type);
            data.append('value', value);
            return post('blacklist_tree_handler', data)
            .then(() => updateBlacklist())
        }

        const manage_course_blacklist = (course) => {
            manage_element_blacklist("courses", !course.blacklisted, course.id)
        }

        store.watch(state => state.blacklistData, blacklistData => {
            updateDisplayedBlacklist(blacklistData);
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
            category_id,
            strings,
            tree,
            expanded,
            blacklisted,
            loadedChildren,
            loadChildren,
            hasChildren,
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
        parent_id : {
            required : true,
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
