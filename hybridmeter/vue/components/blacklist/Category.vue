<template>
    <div class="category">
        <div v-if="expanded">
            <div v-for="category in tree.categories" :key="category.id" class="category_item">
                <i :title="title_category(category)" class="icon fa fa-fw " :class="class_eye_blacklist(category)" @click="manage_category_blacklist(category)" ></i>
                <i class="icon fa fa-fw " :class="category_caret(category)" @click="category.expanded = !category.expanded"></i>
                <span style="font-weight: bold">{{category.name}}</span>
                <category :id="category.id"  :global_blacklist="category.blacklisted" :expanded="category.expanded"></category>
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
import utils from '../../utils.js'
import { sprintf } from 'sprintf-js'

export default {
    setup(props) {
        const { get, post, getStrings } = utils();
        
        const tree = ref({
            categories : [],
            courses : [],
        })
        const config = ref({})
        const strings = ref([])

        const load = async () => {
            let keys = ["blacklist", "whitelist", "x_category", "x_course", "diagnostic_course"];
            let loadstrings = getStrings(keys).then(output => strings.value = output);

            await get("configuration_handler").then(output => config.value = output);

            let data = new FormData();
            data.append('task', 'category_children');
            data.append('id', props.id);

            let loadtree = post('blacklist_tree_handler', data).then(data => {
                
                for (var i in data.categories) {
                    data.categories[i].expanded = false;
                    if (config.value.blacklisted_categories) {
                        if (Object.keys(config.value.blacklisted_categories).includes(data.categories[i].id))
                            data.categories[i].blacklisted = true;
                        else
                            data.categories[i].blacklisted = false;
                    }
                }

                //Apply blacklisted status on loaded courses
                if (config.value.blacklisted_courses) {
                    for (var i in data.courses) {
                        if (Object.keys(config.value.blacklisted_courses).includes(data.courses[i].id))
                            data.courses[i].blacklisted = true;
                        else
                            data.courses[i].blacklisted = false;
                    }
                }

                tree.value = data;
            });

            //TODO : if development mode
            Promise.allSettled([loadstrings, loadtree]).then(console.log("category n°"+props.id+" loaded"));
        }

        function manage_element_blacklist(type, element) {
            var value = !element.blacklisted;
            var data = new FormData();
            data.append('task', 'manage_blacklist');
            data.append('id', element.id);
            data.append('type', type);
            data.append('value', value);
            return post('blacklist_tree_handler', data)
            .then(res => {element.blacklisted = res.blacklisted})
            .then(tree.value = Object.assign({}, tree.value));
        }

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

        const global_blacklist = toRef(props, 'global_blacklist')
        watch(global_blacklist, load)

        return {
            tree,
            config,
            strings,
            get,
            post,
            getStrings,
            load,
            manage_course_blacklist,
            manage_category_blacklist,
            class_eye_blacklist,
            category_caret,
            title_category,
            title_course,
        }
    },
    props : {
        id : {
            required : true,
        },
        global_blacklist : {
            default : false,
        },
        expanded : {
            default : false,
        },
        root : {
            default : false,
        },
    },
    created() {
        this.load();
    },
    name : "Category",
}
</script>
