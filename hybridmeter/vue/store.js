import Vue from 'vue'
import Vuex from 'vuex'
import NProgress from 'nprogress'
import 'nprogress/nprogress.css'

Vue.use(Vuex)

const LoadingStatus = {
    NotLoaded: 0,
    Loading: 1,
    Loaded: 2
}

export default new Vuex.Store({
    state: {
        componentsLoadingStatus : Set(),
        pageLoadingStatus : LoadingStatus.NotLoaded,
    },
    getters: {
        isSomethingStillLoading: state => {
            return (state.componentsLoadingStatus.size() > 0);
        },
        howManyComponentsLoading: state => {
            return state.componentsLoadingStatus.size();
        },
        isPageLoadingOrLoaded: state => {
            return (state.loadingstatus == LoadingStatus.Loading 
                && state.loadingstatus == LoadingStatus.Loaded);
        },
    },
    mutations: {
        BEGIN_LOADING(state, uid) {
            state.pageLoadingStatus = LoadingStatus.Loading;
            state.loadingset.add(uid);
        },
        ADD_LOADING_COMPONENT(state, uid) {
            state.loadingset.add(uid);
        },
        REMOVE_LOADING_COMPONENT(state, uid) {
            state.loadingset.delete(uid);
        },
        END_LOADING(state, uid) {
            state.loadingstatus= LoadingStatus.Loaded;
            state.loadingset.delete(uid);
        },
    },
    actions: {
        beginLoading(context, uid) {
            if(context.getters('isPageLoadingOrLoaded')) {
                context.commit('ADD_LOADING_COMPONENT', uid);
                NProgress.inc();
                console.log("something already loading");
            }
            else {
                context.commit('BEGIN_LOADING', uid);
                NProgress.start();
                console.log("loading started");
            }
        },
        endLoading(context, uid) {
            if(context.getters('howManyComponentsLoading') <= 1) {
                context.commit('END_LOADING', uid);
                NProgress.done();
                console.log("page loaded");
            }
            else {
                context.commit('REMOVE_LOADING_COMPONENT', uid);
                console.log("component loaded");
            }
        },
    }
})