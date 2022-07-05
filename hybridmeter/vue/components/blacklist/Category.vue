<template>
    <div class="category">
        <p>lol</p>
        <div v-if="expanded">
            <div v-for="category in tree.categories" :key="category.id" class="category_item">
                <i :title="title_category(category)" class="icon fa fa-fw " :class="class_eye_category_blacklist(category)" @click="manage_category_blacklist(category)" ></i>
                <i class="icon fa fa-fw " :class="category_caret(category)" @click="category.expanded = !category.expanded"></i>
                <span style="font-weight: bold">{{category.name}}</span>
                <category :id="category.id"  :global_blacklist="category.blacklisted" :expanded="category.expanded"></category>
            </div>
            <div v-for="course in tree.courses" :key="course.id" class="category_item" >
                <i :title="title_course(course)" class="icon fa fa-fw " :class="class_eye_course_blacklist(course)" @click="manage_course_blacklist(course)" ></i>
                <a :title="strings['diagnostic_course']" :href="'tests.php?task=course&id='+course.id"><i class="icon fa fa-fw fa-medkit"></i></a>
                {{course.fullname}}
            </div>
        </div>
    </div>
</template>

<script>
import utils from '../../utils.js'
import { sprintf } from 'sprintf-js'

export default {
    setup() {
        const { get, post, getStringsVue } = utils();
        console.log("lol");
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
            loaded : false,
            tree : {
                categories : [],
                courses : [],
            },
            config : {},
            strings : [],
        }
    },
    created() {
        this.load();
        console.log("?"+this.id);
        console.log(this.expanded);
        console.log(this.tree.categories);
    },
    methods : {
        load : function() {
            let keys = ["blacklist", "whitelist", "x_category", "x_course", "diagnostic_course"];
            let loadstrings = this.getStringsVue(keys).then(strings => this.strings = strings);

            this.config = this.get("configuration_handler");

            let data = new FormData();
            data.append('task', 'category_children');
            data.append('id', this.id);

            let loadtree = this.post('blacklist_tree_handler', data).then(data => {
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

                this.tree = data;
            }).then(console.log(this.tree));

            Promise.allSettled([loadstrings, loadtree]).then(this.loaded=true);
        },
        switch : function() {
            this.expanded = !this.expanded;
        },
        manage_element_blacklist : function(type, element) {
            var value = !element.blacklisted;
            var data = new FormData();
            data.append('task', 'manage_blacklist');
            data.append('id', element.id);
            data.append('type', type);
            data.append('value', value);
            this.post('blacklist_tree_handler',data).then(blacklisted => element.blacklisted = blacklisted);
            this.tree = Object.assign({}, this.tree);
        },
        manage_category_blacklist : function(element){//category) {
            //this.manage_element_blacklist("categories", category);
            var value = !element.blacklisted;
            var data = new FormData();
            data.append('task', 'manage_blacklist');
            data.append('id', element.id);
            data.append('type', "categories");
            data.append('value', value);
            this.post('blacklist_tree_handler',data).then(blacklisted => element.blacklisted = blacklisted);
            this.tree = Object.assign({}, this.tree);
        },
        manage_course_blacklist : function(course) {
            this.manage_element_blacklist("courses", course);
        },
        //Utilitaries functions
        class_eye_category_blacklist : function(item) {
            var blacklisted = item.blacklisted;
            return {
                "fa-eye": !blacklisted,
                "fa-eye-slash": blacklisted,
            }
        },
        class_eye_course_blacklist : function(item) {
            var blacklisted = item.blacklisted;
            return {
                "fa-eye": !blacklisted,
                "fa-eye-slash": blacklisted,
            }
        },
        category_caret(category) {
            return { 
                "fa-caret-down": category.expanded,
                "fa-caret-right": !category.expanded
            };
        },
        title_category : function(category) {
            let x = category.blacklisted ? this.strings["whitelist"] : this.strings["blacklist"];
            return sprintf(this.strings["x_category"], x);
        },
        title_course : function(course) {
            let x = course.blacklisted ? this.strings["whitelist"] : this.strings["blacklist"];
            return sprintf(this.strings["x_course"], x);
        },
    },
    name : "Category",
}
</script>
