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
          <i :title="title_blacklist(category)+' la catégorie'" class="icon fa fa-fw " :class="class_eye_category_blacklist(category)" @click="manage_category_blacklist(category)" ></i>
          <i class="icon fa fa-fw " :class="category_caret(category)" @click="category.expanded = !category.expanded"></i>
          <span style="font-weight: bold">{{category.name}}</span>
          <category :id="category.id"  :global_blacklist="category.blacklisted" :expanded="category.expanded"></category>
        </div>
        <div v-for="course in tree.courses" :key="course.id" class="category_item" >
          <i :title="title_blacklist(course)+' le cours'" class="icon fa fa-fw " :class="class_eye_course_blacklist(course)" @click="manage_course_blacklist(course)" ></i>
          <a title="Obtenir un diagnostic pour le cours" :href="'tests.php?task=course&id='+course.id"><i class="icon fa fa-fw fa-medkit"></i></a>
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
      test : [],
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
    },
    async manage_course_blacklist(course) {
      var value = !course.blacklisted;
      // TODO: Utiliser encodeURI()
      var data = new FormData();
      data.append('task', 'manage_blacklist');
      data.append('id', course.id);
      data.append('type', 'courses');
      data.append('value', value);
      course.blacklisted = (await this.post('blacklist_tree_handler.php',data)).blacklisted;
      this.tree = Object.assign({}, this.tree);
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
      var blacklisted = item.blacklisted;
      return {
        "fa-eye": !blacklisted,
        "fa-eye-slash": blacklisted,
      }
    },
    title_blacklist(item) {
      var blacklisted = item.blacklisted;
      return (
        blacklisted
        ? "Whitelister"
        : "Blacklister"
      )
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
  props: ['boxok', 'scheduled'],
  template: ` 
    <div>
       <!-- TODO: Widget date input -->
      <div v-if="okmesure" v-html="boxok">
        
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
        <!--
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
          -->
        </div>
        <div class="form-item row">
          <div class="form-label col-sm-3 text-sm-right"></div>
          <div class="form-setting col-sm-9">
            <button type="submit" class="btn btn-primary" @click="saveMesure">Enregistrer les modifications</button>
          </div>
        </div>
      </div>
      <hr/>

      <!-- Configuration lancement -->
      <div v-if="oklancement">
        <p>La date de lancement a bien été programmée pour le {{ scheduled_date_formatted }} à {{ scheduled_time }}</p>
      </div>
      <div v-if="okclosed">
        <p>Le lancement a été déprogrammé avec succès</p>
      </div>
      <h3 class="main">Prochain lancement</h3>
      <div id="schedule" class="management-module">
        <div style="margin-bottom : 10px;" class="form-item row">
          <div class="form-label col-sm-3 text-sm-right">
          </div>
          <div class="form-setting col-sm-9">
            <span>
                <button style="" @click="del_messages(); set_tomorrow_midnight();">Cette nuit</button>
                <button style=""  @click="del_messages(); set_saturday_midnight();">Ce week-end</button>
            </span>
          </div>
        </div>
        <div class="form-item row">
          <div class="form-label col-sm-3 text-sm-right">
            <label>
              Date de lancement
            </label>
          </div>
          <div class="form-setting col-sm-9">
            <div class="form-text defaultsnext">
              <input type="date" v-model="scheduled_date" @change="del_messages()">
            </div>
          </div>
        </div>
        <div class="form-item row">
          <div class="form-label col-sm-3 text-sm-right">
            <label>
              Heure de lancement
            </label>
          </div>
          <div class="form-setting col-sm-9">
            <div class="form-text defaultsnext">
              <input type="time"  v-model="scheduled_time" @change="del_messages()">
            </div>
          </div>
        </div>
        <div class="form-item row">
          <div class="form-label col-sm-3 text-sm-right"></div>
          <span class="form-setting col-sm-9">
            <button :disabled="!datetime_filled" type="submit" class="btn btn-primary" @click="saveLancement">Programmer le lancement</button>
            <button :hidden="scheduled == 0" type="submit" style="vertical-align: bottom;" class="btn btn-secondary" @click="closeLancement">Déprogrammer le lancement</button>
          </span>
        </div>
      </div>

      <!-- Configuration additionnelle -->
      <h3 class="main">Configuration additionnelle</h3>
      <div v-if="okadditionalconfig">
        <p>La configuration a été sauvegardée avec succès</p>
      </div>
      <div class="management-module">
        <div style="margin-bottom : 10px;" class="form-item row">
          <div class="form-label col-sm-3 text-sm-right">
            <label>Rôle étudiant</label>
          </div>
          <div class="form-setting col-sm-9">
            <span>
              <select class="form-control" v-model="student_role">
                <option v-for="role in roles" :key="role.id" :value="role.shortname">{{ role.shortname }}</option>
              </select>
            </span>
          </div>
        </div>
        <div class="form-item row">
          <div class="form-label col-sm-3 text-sm-right"></div>
          <div class="form-setting col-sm-9">
            <button type="submit" class="btn btn-primary" @click="saveAdditionalConfig">Enregistrer les modifications</button>
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
      scheduled_date:undefined,
      scheduled_time:undefined,
      today:undefined,
      tomorrow:undefined,
      saturday:undefined,
      okmesure:false,
      oklancement:false,
      okadditionalconfig: false,
      okclosed:false,
      action:undefined,
      roles: [],
      student_role: undefined
    }
  },
  watch: {
    config(config) {
      this.begin_date = this.timestamp_to_ui(config.begin_date);
      this.end_date = this.timestamp_to_ui(config.end_date);
      this.debug = config.debug;
      this.student_role = config.student_role;
    },
    begin_date(date) {
      this.config.begin_date = this.ui_to_timestamp(date);
    },
    end_date(date) {
      this.config.end_date = this.ui_to_timestamp(date, true);
    },
    student_role(student_role) {
      this.config.student_role = student_role;
    }
  },
  computed: {
    scheduled_date_formatted : function () {
      var temp_date = new Date(this.scheduled_date);
      let year = temp_date.getFullYear();
      let month = (temp_date.getMonth()+1).toLocaleString('fr-FR', {
        minimumIntegerDigits : 2,
        useGrouping: false
      });
      let day = (temp_date.getDate()).toLocaleString('fr-FR', {
        minimumIntegerDigits : 2,
        useGrouping: false
      });
      return `${day}/${month}/${year}`
    },
    datetime_filled : function() {
      return this.scheduled_date && this.scheduled_time;
    }
  },
  async created() {
    this.today = new Date();
    this.tomorrow = new Date(this.today);
    this.tomorrow.setDate(this.today.getDate() + 1);
    this.scheduled_date = this.date_to_ui(this.tomorrow);
    this.scheduled_time = "02:00";  
    this.config = this.get(`configuration_handler.php`).then((data) => { this.config = data });
    this.roles = this.get(`moodle_data.php?task=roles`).then((data) => { this.roles = data });
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
    set_tomorrow_midnight(){
      this.scheduled_date = this.date_to_ui(this.tomorrow);
      this.scheduled_time = "02:00";
    },
    set_saturday_midnight(){
      if(this.saturday == undefined){
        let delta = 6 - this.today.getDay();
        this.saturday = new Date();
        this.saturday.setDate(this.today.getDate() + delta);
      }
      this.scheduled_date = this.date_to_ui(this.saturday);
      this.scheduled_time = "02:00";
    },
    date_to_ui(date){
      let year = date.getFullYear();
      let month = (date.getMonth()+1).toLocaleString('fr-FR', {
        minimumIntegerDigits : 2,
        useGrouping: false
      });
      let day = (date.getDate()).toLocaleString('fr-FR', {
        minimumIntegerDigits : 2,
        useGrouping: false
      });
      return `${year}-${month}-${day}`;
    },
    del_messages(){
      this.okclosed = false;
      this.oklancement = false;
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
    ui_to_timestamp(text, is_end_date = false) {
      if(is_end_date){
        text = text+' 23:59:59';
      }
      var date = new Date(text);
      return date.getTime() / 1000;
    },
    async saveMesure() {
      this.action="periode_mesure";
      var data = new FormData();
      data.append('action', this.action);
      data.append('begin_date', this.config.begin_date);
      data.append('end_date', this.config.end_date);
      data.append('debug', this.config.debug);
      await this.post(`configuration_handler.php`, data);
      this.okmesure=true;
    },
    async saveAdditionalConfig() {
      this.action="additional_config";
      var data = new FormData();
      data.append('action', this.action);
      data.append('student_role', this.student_role);
      await this.post(`configuration_handler.php`, data);
      this.okadditionalconfig=true;
    },
    async saveLancement() {
      this.action="schedule";
      var data = new FormData();
      let timestamp = Date.parse(this.scheduled_date+' '+this.scheduled_time) / 1000;
      data.append('action', this.action);
      data.append('scheduled_timestamp', timestamp);
      data.append('debug', this.config.debug);
      await this.post(`configuration_handler.php`, data);
      this.oklancement=true;
      this.okclosed=false;
      this.scheduled=1;
    },
    async closeLancement() {
      this.action="unschedule";
      var data = new FormData();
      data.append('action', this.action);
      data.append('debug', this.config.debug);
      this.scheduled_date = undefined;
      this.scheduled_time = undefined;
      await this.post(`configuration_handler.php`, data);
      this.oklancement=false;
      this.okclosed=true;
      this.scheduled=0;
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
