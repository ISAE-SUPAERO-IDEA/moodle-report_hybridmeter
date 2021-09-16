// Retrieve strings or file roots (TODO: improve P2)
var url_root = document.getElementById('www_root').value;

// Blacklist tree
Vue.component('category', {
  props: ['id', 'global_blacklist', 'expanded'],
  template: ` 
    <div>
      <div v-if="expanded">
        <div v-for="category in tree.categories" :key="category.id" class="category_item">
          <i class="icon fa fa-fw " :class="class_eye_category_blacklist(category)" @click="manage_category_blacklist(category)" ></i>
          <i class="icon fa fa-fw " :class="category_caret(category)" @click="category.expanded = !category.expanded"></i>
          <span style="font-weight: bold">{{category.name}}</span>
          <category :id="category.id"  :global_blacklist="category.blacklisted" :expanded="category.expanded"></category>
        </div>
        <div v-for="course in tree.courses" :key="course.id" class="category_item" >
          <i class="icon fa fa-fw " :class="class_eye_course_blacklist(course)" @click="manage_course_blacklist(course)" ></i>
          {{course.fullname}}
        </div>
      </div>
    </div>
    `,
  data() {
    return {
      tree: {
        categories:[],
        courses:[]
      },
      config: {}
    }
  },
  async created() {
    this.load();
  },
  watch: {
    global_blacklist: function(){
      this.load();
    }
  },
  methods: {
    load: async function(){
      // TODO: Utiliser encodeURI() 
      this.config = await this.get(`configuration_handler.php`);
      var data = new FormData();
      data.append('task', 'category_children');
      data.append('id', this.id);
      this.tree = await this.post('blacklist_tree_handler.php',data).then(data => {
        //this.tree = await this.get(`blacklist_tree_handler.php?task=category_children&id=${this.id}`).then(data => {
        // TODO: fonction générique
        for (var i in data.categories) {
          data.categories[i].expanded = false;
          if (this.config.blacklisted_categories) {
            if (Object.keys(this.config.blacklisted_categories).includes(data.categories[i].id)) {
              data.categories[i].blacklisted = true;
            }
          }
        }
        if (this.config.blacklisted_courses) {
          for (var i in data.courses) {
            if (Object.keys(this.config.blacklisted_courses).includes(data.courses[i].id)) {
              data.courses[i].blacklisted = true;
            }
          }
        }

        return data;

      });

      console.log(this.tree);
    },
    get: function (urlRequest){
      const myaxios = axios.create({ baseURL: `${url_root}/report/hybridmeter/ajax/` });
      return myaxios.get(urlRequest).then(response => response.data)
    },
    post: function(url, data){
      const myaxios = axios.create({ baseURL: `${url_root}/report/hybridmeter/ajax/`});
      return myaxios.post(url, data).then(response => response.data)
    },
    switch: function() {
      this.expanded = !this.expanded;
    },
    async manage_category_blacklist(category) {
      var value = !category.blacklisted;
      // TODO: Utiliser encodeURI()
      var data = new FormData();
      data.append('task', 'manage_blacklist');
      data.append('id', category.id);
      data.append('type', 'categories');
      data.append('value', value);
      category.blacklisted = (await this.post('blacklist_tree_handler.php',data)).blacklisted;
      this.tree = Object.assign({}, this.tree);
      console.log(this.tree);
    },
    async manage_course_blacklist(course) {
      if (!this.global_blacklist) {
        var value = !course.blacklisted;
        // TODO: Utiliser encodeURI()
        var data = new FormData();
        data.append('task', 'manage_blacklist');
        data.append('id', course.id);
        data.append('type', 'courses');
        data.append('value', value);
        course.blacklisted = (await this.post('blacklist_tree_handler.php',data)).blacklisted;
        this.tree = Object.assign({}, this.tree);
      }
    },
    class_eye_category_blacklist(item) {
      var blacklisted = item.blacklisted;
      return {
        "fa-eye": !blacklisted,
        "fa-eye-slash": blacklisted,
      }
    },
    class_eye_course_blacklist(item) {
      var blacklisted = item.blacklisted || this.global_blacklist == true;
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
    }
  },
});
// Configurator
Vue.component('configurator', {
  props: ['boxok', 'boxnotok'],
  template: ` 
    <div>
       <!-- TODO: Widget date input -->
      <div v-if="ok" v-html="boxok">
        
      </div>
      <div class="form-item row">
        <div class="form-label col-sm-3 text-sm-right">
          <label>
            Date de début
          </label>
        </div>
        <div class="form-setting col-sm-9">
          <div class="form-text defaultsnext">
            <input type="date"  v-model="begin_date">
          </div>
        </div>
      </div>
      <div class="form-item row">
        <div class="form-label col-sm-3 text-sm-right">
          <label>
            Date de fin
          </label>
        </div>
        <div class="form-setting col-sm-9">
          <div class="form-text defaultsnext">
            <input type="date"  v-model="end_date">
          </div>
        </div>
      </div>
      <div class="form-item row">
        <div class="form-label col-sm-3 text-sm-right"></div>
        <div class="form-setting col-sm-9">
          <button type="submit" class="btn btn-primary" @click="save">Enregistrer les modifications</button>
        </div>
      </div>
      <!--<hr/>
      <h3 class="main">Configuration des poids</h3>
      <span>Coming soon</span>-->
    </div>
    `,
  data() {
    return {
      config: {},
      begin_date:undefined,
      end_date:undefined,
      ok:false
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
  },
  methods: {
    // TODO: Utiliser un mixin pour les fonctions de bases get et post
    get: function (urlRequest){
      const myaxios = axios.create({ baseURL: `${url_root}/report/hybridmeter/ajax/` });
      return myaxios.get(urlRequest).then(response => response.data)
    },
    post: function (urlRequest, data){
      const myaxios = axios.create({ baseURL: `${url_root}/report/hybridmeter/ajax/` });
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
      this.ok=true;
    }
  }
});

console.log("check");

Vue.component("test-grid", {
  template: `<table>
        <thead>
          <tr>
            <th v-for="key in columns"
              @click="sortBy(key)"
              :class="{ active: sortKey == key }">
              {{ key | capitalize }}
              <span class="arrow" :class="sortOrders[key] > 0 ? 'asc' : 'dsc'">
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="entry in filteredElements">
            <td v-for="key in columns">
              {{entry[key]}}
            </td>
          </tr>
        </tbody>
      </table>`,
  props: ['task', 'sortKey', 'url'],
  data() {
    return {
      columns : undefined,
      rows : undefined,
      sortOrders: {}
    }
  },
  async created(){
    var data = await this.get_data(this.url, this.task);
    console.log("gaco "+data.columns[0]);;
    
    var sortOrders = {};
    data.columns.forEach(function(key) {
      sortOrders[key] = 1;
    });

    this.sortOrders = sortOrders;
    this.columns = data.columns;
    this.rows = data.rows;
  },
  computed: {
    filteredElements: function() {
      var sortKey = this.sortKey;
      var filterKey = this.filterKey && this.filterKey.toLowerCase();
      var order = this.sortOrders[sortKey] || 1;
      var rows = this.rows;
      if (filterKey) {
        rows = rows.filter(function(row) {
          return Object.keys(row).some(function(key) {
            return (
              String(row[key])
                .toLowerCase()
                .indexOf(filterKey) > -1
            );
          });
        });
      }
      if (sortKey) {
        rows = rows.slice().sort(function(a, b) {
          a = a[sortKey];
          b = b[sortKey];
          return (a === b ? 0 : a > b ? 1 : -1) * order;
        });
      }
      console.log(rows);
      return rows;
    }
  },
  filters: {
    capitalize: function(str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    }
  },
  methods: {
    get_data: function(url, task){
      const myaxios = axios.create({ baseURL: `${url_root}/report/hybridmeter/ajax/` });
      return myaxios.get(`${url}?task=${task}`).then(response => response.data)
    },
    sortBy: function(key) {
      this.sortKey = key;
      this.sortOrders[key] = this.sortOrders[key] * -1;
    }
  }
});

// Deploy ap
var app = new Vue({
  el: '#app'
});
