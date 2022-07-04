<template>
    <div class="category">
        <div v-if="expanded">
            <!--<div v-for="category in tree.categories" :key="category.id" class="category_item">
                <i :title="title_blacklist(category)+' la catégorie'" class="icon fa fa-fw " :class="class_eye_category_blacklist(category)" @click="manage_category_blacklist(category)" ></i>
                <i class="icon fa fa-fw " :class="category_caret(category)" @click="category.expanded = !category.expanded"></i>
                <span style="font-weight: bold">{{category.name}}</span>
                <category :id="category.id"  :global_blacklist="category.blacklisted" :expanded="category.expanded"></category>
            </div>
            <div v-for="course in tree.courses" :key="course.id" class="category_item" >
                <i :title="title_blacklist(course)+' le cours'" class="icon fa fa-fw " :class="class_eye_course_blacklist(course)" @click="manage_course_blacklist(course)" ></i>
                <a title="Obtenir un diagnostic pour le cours" :href="'tests.php?task=course&id='+course.id"><i class="icon fa fa-fw fa-medkit"></i></a>
                {{course.fullname}}
            </div>-->
        </div>
    </div>
</template>

<script>
import utils from '../../utils.js'
import { sprintf } from 'sprintf-js'

export default {
    setup() {
        const { get, post, getStringsVue } = utils();
        return {
            get,
            post,
            getStringsVue,
        }
    },
    props : {
        id : {
            type : Number,
            required : true,
        },
        global_blacklist : {
            type : Boolean,
            default : false,
        },
        expanded : {
            type : Boolean,
        },
        root : {
            type : Boolean,
            default : false,
        },
    },
    data() {
        return {
            tree : {
                categories : [],
                courses : [],
            },
            config : {},
            strings : [],
        }
    },
    created() {
        await this.load();
        this.loading = false;
    },
    computed : {

    },
    methods : {
        load : async function() {
            let keys = ["blacklist", "whitelist", "x_category", "x_course", "diagnostic_course"];
            this.getStringsVue(keys).then(strings => this.strings = strings);

            this.config = this.get("configuration_handler");

            let data = new FormData();
            data.append('task', 'category_children');
            data.append('id', this.id);

            this.tree = await this.post('blacklist_tree_handler', data).then(data => {
                this.test = data.courses;
                //Apply blacklisted status on loaded categories
                for (var i in data.categories) {
                    data.categories[i].expanded = false;
                    if (this.config.blacklisted_categories) {
                        if (Object.keys(this.config.blacklisted_categories).includes(data.categories[i].id)) {
                            data.categories[i].blacklisted = true;
                        }
                    }
                }

                //Apply blacklisted status on loaded courses
                if (this.config.blacklisted_courses) {
                    for (var i in data.courses) {
                        if (Object.keys(this.config.blacklisted_courses).includes(data.courses[i].id)) {
                            data.courses[i].blacklisted = true;
                        }
                    }
                }

                return data;
            });
        },
        switch: function() {
            this.expanded = !this.expanded;
        },
        async manage_element_blacklist(type, element) {
            var value = !element.blacklisted;
            var data = new FormData();
            data.append('task', 'manage_blacklist');
            data.append('id', element.id);
            data.append('type', type);
            data.append('value', value);
            element.blacklisted = (await this.post('blacklist_tree_handler',data)).blacklisted;
            this.tree = Object.assign({}, this.tree);
        },
        async manage_category_blacklist(category) {
            await this.manage_element_blacklist("categories", category);
        },
        async manage_course_blacklist(course) {
            await this.manage_element_blacklist("courses", course);
        }
    },
    name : "Category",
}
</script>
