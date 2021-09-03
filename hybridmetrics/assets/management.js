// Retrieve strings or file roots (TODO: improve P2)
var url_root = document.getElementById('www_root').value;

// Blacklist tree
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
    // TODO: Utiliser encodeURI() 
    this.tree = await this.get(`blacklist_tree_handler.php?task=category_children&id=${this.id}`);
    console.log(this.tree);
  },
  methods: {
    get: function (urlRequest){
      const myaxios = axios.create({ baseURL: `${url_root}/report/hybridmetrics/ajax/` });
      return myaxios.get(urlRequest).then(response => response.data)
    },
    switch: function() {
      this.expanded = !this.expanded;
    },
    async manage_category_blacklist(category) {
      if (!this.global_blacklist) {
        var value = !category.blacklisted;
        // TODO: Utiliser encodeURI() 
        category.blacklisted = (await this.get(`blacklist_tree_handler.php?task=manage_blacklist&type=category&id=${category.id}&value=${value}`)).blacklisted;
        this.tree = Object.assign({}, this.tree);
        console.log(this.tree);
      }
    },
    async manage_course_blacklist(course) {
      if (!this.global_blacklist) {
        var value = !course.blacklisted;
        // TODO: Utiliser encodeURI() 
        course.blacklisted = (await this.get(`blacklist_tree_handler.php?task=manage_blacklist&type=course&id=${course.id}&value=${value}`)).blacklisted;
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
// Configurator
Vue.component('configurator', {
  template: ` 
    <div>
    <!-- TODO: Widget date input -->
      <div class="form-item row" id="admin-naas_endpoint">
        <div class="form-label col-sm-3 text-sm-right">
          <label for="id_s_naas_naas_endpoint">
            Date de début
          </label>
        </div>
        <div class="form-setting col-sm-9">
          <div class="form-text defaultsnext">
            <input type="date"  v-model="begin_date">
          </div>
        </div>
      </div>
      <div class="form-item row" id="admin-naas_endpoint">
        <div class="form-label col-sm-3 text-sm-right">
          <label for="id_s_naas_naas_endpoint">
            Date de fin
          </label>
        </div>
        <div class="form-setting col-sm-9">
          <div class="form-text defaultsnext">
            <input type="date"  v-model="end_date">
          </div>
        </div>
      </div>
      <div class="form-item row" id="admin-naas_endpoint">
        <div class="form-label col-sm-3 text-sm-right"></div>
        <div class="form-setting col-sm-9">
          <button type="submit" class="btn btn-primary" @click="save">Enregistrer les modifications</button>
        </div>
      </div>
    </div>
    `,
  data() {
    return {
      config: {},
      begin_date:undefined,
      end_date:undefined
    }
  },
  watch: {
    config(config) {
      this.begin_date = this.timestamp_to_ui(config.begin_date);
      this.end_date = this.timestamp_to_ui(config.end_date);
    },
    begin_date(date) {
      this.config.begin_date = this.ui_to_timestamp(date);
    },
    end_date(date) {
      this.config.end_date = this.ui_to_timestamp(date);
    }
  },
  async created() {
    // TODO: Utiliser encodeURI() 
    this.config = await this.get(`configuration_handler.php`);
    console.log(this.config);
  },
  methods: {
    // TODO: Utiliser un mixin pour les fonctions de bases get et post
    get: function (urlRequest){
      const myaxios = axios.create({ baseURL: `${url_root}/report/hybridmetrics/ajax/` });
      return myaxios.get(urlRequest).then(response => response.data)
    },
    post: function (urlRequest, data){
      const myaxios = axios.create({ baseURL: `${url_root}/report/hybridmetrics/ajax/` });
      return myaxios.post(urlRequest, data).then(response => response.data)
    },
    timestamp_to_ui(timestamp) {
      // Create a new JavaScript Date object based on the timestamp
      // multiplied by 1000 so that the argument is in milliseconds, not seconds.
      var date = new Date(timestamp * 1000);
      let ye = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(date);
      let mo = new Intl.DateTimeFormat('en', { month: '2-digit' }).format(date);
      let da = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(date);

      // Will display time in 10:30:23 format
      var formattedTime = `${ye}-${mo}-${da}`;

      return formattedTime;
    },
    ui_to_timestamp(text) {
      var date = new Date(text);
      return date.getTime() / 1000;
    },
    async save() {
      var data = new FormData();
      data.append('begin_date', this.config.begin_date);
      data.append('end_date', this.config.end_date);
      await this.post(`configuration_handler.php`, data);
    }
  }
});

// Deploy ap
var app = new Vue({
  el: '#app',
});
