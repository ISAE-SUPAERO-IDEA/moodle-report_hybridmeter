// Retrieve strings or file roots, passer www_root en props (TODO: improve P2)
var url_root = document.getElementById('www_root').value;

//Loading bar, author : github @greyby project : Vue Spinner
Vue.component('PulseLoader',{
  template: `<div class="v-spinner" v-show="loading">
      <div class="v-pulse v-pulse1" v-bind:style="[spinnerStyle,spinnerDelay1]">
      </div><div class="v-pulse v-pulse2" v-bind:style="[spinnerStyle,spinnerDelay2]">
      </div><div class="v-pulse v-pulse3" v-bind:style="[spinnerStyle,spinnerDelay3]">
      </div>
    </div>`,
  props: {
    loading: {
      type: Boolean,
      default: true
    },
    color: { 
      type: String,
      default: '#5dc596'
    },
    size: {
      type: String,
      default: '15px'
    },
    margin: {
      type: String,
      default: '2px'
    },
    radius: {
      type: String,
      default: '100%'
    }
  },
  data() {
    return {
      spinnerStyle: {
        backgroundColor: this.color,
        width: this.size,
        height: this.size,
        margin: this.margin,
        borderRadius: this.radius,
        display: 'inline-block',
        animationName: 'v-pulseStretchDelay',
        animationDuration: '0.75s',
        animationIterationCount: 'infinite',
        animationTimingFunction: 'cubic-bezier(.2,.68,.18,1.08)',
        animationFillMode: 'both'
      },
      spinnerDelay1: {
        animationDelay: '0.12s'
      },
      spinnerDelay2: {
        animationDelay: '0.24s'
      },
      spinnerDelay3: {
        animationDelay: '0.36s'
      }
    }
  }
});

// Blacklist tree
Vue.component('category', {
  template: ` 
    <div>
      <div v-bind:aria-hidden="[!loading || !root]" class="loader">
        <PulseLoader :color="'#00acdf'"></PulseLoader>
      </div>
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
  props: {
    id: {
      type: Number
    },
    global_blacklist : {
      type: Boolean,
      default : false
    },
    expanded : {
      type : Boolean
    },
    root : {
      type : Boolean,
      default : false
    }
  },
  data() {
    return {
      tree: {
        categories:[],
        courses:[]
      },
      config: {},
      loading : true
    }
  },
  async created() {
    await this.load();
    this.loading = false;
  },
  watch: {
    //reload every component recursively in case of blacklisted state change
    global_blacklist: function(){
      this.load();
    }
  },
  methods: {
    load: async function(){
      // TODO: Utiliser encodeURI() 
      //Load config
      this.config = await this.get(`configuration_handler.php`);
      var data = new FormData();
      data.append('task', 'category_children');
      data.append('id', this.id);
      //Load category tree
      this.tree = await this.post('blacklist_tree_handler.php',data).then(data => {
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
    //TODO : utiliser mixin
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
    /*TODO : Généraliser ces deux fonctions VVVV*/
    async manage_category_blacklist(category) {
      var value = !category.blacklisted;
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
    //Utilitaries functions
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
/*TODO : Envoyer string traduits par moodle*/
Vue.component('configurator', {
  props: ['boxok'],
  template: ` 
    <div>
       <!-- TODO: Widget date input -->
      <div v-if="ok" v-html="boxok">
        
      </div>
      <h3 class="main">Période de mesure</h3>
      <div id="plage" class="management-module">
      <!-- Date de début -->
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
        <!-- Date de fin -->
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
        <!-- Debug -->
          <div class="form-label col-sm-3 text-sm-right">
            <label>
              Loggeur
            </label>
          </div>
          <div class="form-setting col-sm-9">
            <div class="form-text defaultsnext">
              <input type="checkbox" v-model="config.debug">
              <label for="checkbox">{{  }}</label>
            </div>
          </div>
        </div>
        <div class="form-item row">
          <div class="form-label col-sm-3 text-sm-right"></div>
          <div class="form-setting col-sm-9">
            <button type="submit" class="btn btn-primary" @click="save">Enregistrer les modifications</button>
          </div>
        </div>

      </div>
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
      this.debug = config.debug;
    },
    begin_date(date) {
      this.config.begin_date = this.ui_to_timestamp(date);
    },
    end_date(date) {
      this.config.end_date = this.ui_to_timestamp(date);
    }
  },
  async created() {
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
      data.append('debug', this.config.debug);
      await this.post(`configuration_handler.php`, data);
      this.ok=true;
    }
  }
});

//Table component
Vue.component("test-grid", {
  template: `
    <div>
      <div v-bind:aria-hidden="[!loading]" class="loader">
        <PulseLoader :color="'#00acdf'"></PulseLoader>
      </div>
      <table>
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
      </table>
    </div>`,
  props: ['task', 'sortKey', 'url'],
  data() {
    return {
      columns : undefined,
      rows : undefined,
      sortOrders: {},
      loading: true
    }
  },
  async created(){
    var data = await this.get_data(this.url, this.task);
    
    var sortOrders = {};
    data.columns.forEach(function(key) {
      sortOrders[key] = 1;
    });

    this.sortOrders = sortOrders;
    this.columns = data.columns;
    this.rows = data.rows;
    this.loading=false;
  },
  computed: {
    filteredElements: function() {
      var sortKey = this.sortKey;
      var filterKey = this.filterKey && this.filterKey.toLowerCase();
      var order = this.sortOrders[sortKey] || 1;
      var rows = this.rows;

      //Filtrage
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

      //tri
      if (sortKey) {
        rows = rows.slice().sort(function(a, b) {
          a = a[sortKey];
          b = b[sortKey];
          return (a === b ? 0 : a > b ? 1 : -1) * order;
        });
      }
      
      return rows;
    }
  },
  filters: {
    capitalize: function(str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    }
  },
  methods: {
    //TODO : mixin
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

// Deploy app
var app = new Vue({
  el: '#app'
});
