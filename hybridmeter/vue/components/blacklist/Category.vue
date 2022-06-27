<template>
    <div class="category">
        <!--<div v-if="expanded">
            <div v-for="category in tree.categories" :key="category.id" class="category_item">
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
</template>

<script>
import { sprintf } from 'sprintf-js';



export default {
    props : {
        id: {
            type: Number,
            required:true
        },
    },
    name : "Category",
}
</script>
