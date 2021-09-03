// Retrieve stings or file roots 
var url_root = document.getElementById('www_root').value;

// Card component to display basic nugget  information
Vue.component('category', {
  props: ['id', 'global_blacklist'],
  template: ` 
    <div>
      <div v-for="category in tree.categories" :key="category.id" class="category_item">
        <i class="icon fa fa-fw " :class="class_eye_blacklist(category)" @click="manage_category_blacklist(category)" ></i>
        <i class="icon fa fa-fw " :class="caret" @click="expanded = !expanded"></i>
        {{category.name}}
        <category :id="category.id"  :global_blacklist="category.blacklisted" v-if="expanded"></category>
      </div>
      <div v-for="course in tree.courses" :key="course.id" class="category_item" >
        <i class="icon fa fa-fw " :class="class_eye_blacklist(course)" @click="manage_course_blacklist(course)" ></i>
        {{course.fullname}}
      </div>
    </div>
    `,
  data() {
    return {
      tree: {
        categories:[],
        courses:[]
      },
      expanded: false
    }
  },
  async created() {
    this.tree = await this.get(`/report/hybridmetrics/tree_handler.php?task=category_children&id=${this.id}`);
    console.log(this.tree);
  },
  methods: {
    get: function (urlRequest, initialize){
      const myaxios = axios.create({ baseURL: url_root });
      return myaxios.get(urlRequest).then(response => response.data)
    },
    switch: function() {
      this.expanded = !this.expanded;
    },
    async manage_category_blacklist(category) {
      if (!this.global_blacklist) {
        var value = !category.blacklisted;
        category.blacklisted = (await this.get(`/report/hybridmetrics/tree_handler.php?task=manage_blacklist&type=category&id=${category.id}&value=${value}`)).blacklisted;
        this.tree = Object.assign({}, this.tree);
        console.log(this.tree);
      }
    },
    async manage_course_blacklist(course) {
      if (!this.global_blacklist) {
        var value = !course.blacklisted;
        course.blacklisted = (await this.get(`/report/hybridmetrics/tree_handler.php?task=manage_blacklist&type=course&id=${course.id}&value=${value}`)).blacklisted;
        this.tree = Object.assign({}, this.tree);
      }
    },
    class_eye_blacklist(item) {
      var blacklisted = item.blacklisted || this.global_blacklist == true;
      return {
        "fa-eye": !blacklisted,
        "fa-eye-slash": blacklisted,
      }
    }
  },
  computed: {
    caret() {
      var val = this.expanded ? "up": "down";
      return { 
        "fa-caret-up": this.expanded,
        "fa-caret-down": !this.expanded
      };
    }
  }
});

var app = new Vue({
  el: '#app',
});
