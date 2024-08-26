<!--
 - @author Nassim Bennouar
 - @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 - @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
-->

<template>
    <div class="hybrid-category">
        <i :title="title_category" class="icon fa fa-fw " :class="class_eye_exclusion" @click="manage_category_exclusion()" ></i>
        <i v-if="loadedChildren && hasChildren" class="icon fa fa-fw " :class="category_caret" @click="expanded = !expanded"></i>
        <i v-else-if="!loadedChildren" class="icon fa fa-fw " :class="category_caret"></i>
        <i v-else class="icon fa"></i>
        <span style="font-weight: bold">{{category_id}} {{category_name}}</span>
        <div v-if="expanded">
            <div v-for="category in tree.categories" :key="category.id">
                <category :parent_id="category_id" :category_id="category.id" :category_name="category.name" :strings="strings"></category>
            </div>
            <div v-for="course in tree.courses" :key="course.id" class="hybrid-course" >
                <i :title="title_course(course)" class="icon fa fa-fw " :class="course_class_eye_exclusion(course)" @click="manage_course_exclusion(course)" ></i>
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
        const { get, post, updateExclusions } = utils();
        
        const strings = ref(props.strings);

        const tree = ref({
            categories : [],
            courses : [],
        })

        const category_id = ref(props.category_id);

        const expanded = ref(false)

        const store = useStore()

        const excluded = ref(false)

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

            return get('exclusions_tree_handler', data).then(data => {
                tree.value = data;
                loadedChildren.value = true
            });
        }

        const isExcludedCategory = (exclusionData, category) => {
            if (exclusionData == undefined) {
                throw new Error('exclusions data in unavailable');
            }
            
            if (Object.keys(exclusionData.excluded_categories).includes(category)) {
                return exclusionData.excluded_categories[category];
            }
            else if (props.parent_id == 0) {
                return false;
            }
            else {
                return isExcludedCategory(exclusionData, props.parent_id);
            }
        }

        const isExcludedCourse = (exclusionData, course) => {
            if (exclusionData == undefined) {
                throw new Error('exclusions data is unavailable');
            }

            if (Object.keys(exclusionData.excluded_courses).includes(course)) {
                return exclusionData.excluded_courses[course];
            }
            else {
                return isExcludedCategory(exclusionData, props.category_id);
            }
        }

        const updateDisplayedExclusions = exclusionData => {
            if (exclusionData != undefined) {
                excluded.value = isExcludedCategory(exclusionData, props.category_id);
                
                let courses = tree.value.courses
                for (let i = 0; i<courses.length; i++) {
                    courses[i].excluded = isExcludedCourse(exclusionData, courses[i].id);
                }
            }
        }

        const loadExclusions = () => {
            loadChildren().then(() => {
                
                updateDisplayedExclusions(store.state.exclusionData)
            })
        }

        function manage_element_exclusion(type, value, id) {
            var data = new FormData();
            data.append('task', 'manage_exclusions');
            data.append('id', id);
            data.append('type', type);
            data.append('value', value);
            return post('exclusions_tree_handler', data)
            .then(() => updateExclusions())
        }

        const manage_course_exclusion = (course) => {
            manage_element_exclusion("courses", !course.excluded, course.id)
        }

        store.watch(state => state.exclusionData, exclusionData => {
            updateDisplayedExclusions(exclusionData);
        })

        const manage_category_exclusion = () => {
            manage_element_exclusion("categories", !excluded.value, props.category_id)
        }

        const class_eye_exclusion = computed(() => {
            return (excluded.value ? "fa-eye-slash" : "fa-eye")
        });

        const course_class_eye_exclusion = (course) => {
            return (course.excluded ? "fa-eye-slash" : "fa-eye")
        };

        const category_caret = computed(() => {
            return (expanded.value ? "fa-caret-down" : "fa-caret-right")
        })

        const title_category = computed(() => {
            let x = excluded.value ? strings.value["included_list"] : strings.value["excluded_list"]
            return sprintf(strings.value["x_category"], x)
        })

        const title_course = (course) => {
            let x = course.excluded ? strings.value["included_list"] : strings.value["excluded_list"]
            return sprintf(strings.value["x_course"], x)
        }

        return {
            category_id,
            strings,
            tree,
            expanded,
            excluded: excluded,
            loadedChildren,
            loadChildren,
            hasChildren,
            loadExclusions,
            manage_course_exclusion: manage_course_exclusion,
            manage_category_exclusion: manage_category_exclusion,
            class_eye_exclusion: class_eye_exclusion,
            course_class_eye_exclusion: course_class_eye_exclusion,
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
        this.loadExclusions();
    },
    name : "Category",
}
</script>
