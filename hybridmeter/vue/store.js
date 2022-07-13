import Vuex from 'vuex'
import NProgress from 'nprogress'
import 'nprogress/nprogress.css'

const LoadingStatus = {
    NotLoading: 0,
    Loading: 1,
}

//TODO : Replace the counter by a set identifying every loading unit of the code
//(using this package https://www.npmjs.com/package/uuid).

export default new Vuex.Store({
    state: {
        componentsLoadingStatus : 0,
        pageLoadingStatus : LoadingStatus.NotLoading,
    },
    getters: {
        isSomethingStillLoading: state => {
            return (state.componentsLoadingStatus > 0);
        },
        howManyComponentsLoading: state => {
            return state.componentsLoadingStatus;
        },
        isPageLoading: state => {
            return (state.loadingstatus == LoadingStatus.Loading);
        },
    },
    mutations: {
        BEGIN_LOADING(state) {
            state.pageLoadingStatus = LoadingStatus.Loading;
            state.componentsLoadingStatus++;
        },
        ADD_LOADING_COMPONENT(state) {
            state.componentsLoadingStatus++;
        },
        REMOVE_LOADING_COMPONENT(state) {
            state.componentsLoadingStatus--;
        },
        END_LOADING(state) {
            state.loadingstatus= LoadingStatus.NotLoading;
            state.componentsLoadingStatus=0;
        },
    },
    actions: {
        beginLoading(context) {
            if(!context.getters['isPageLoading']) {
                context.commit('BEGIN_LOADING');
                //console.log("begin loading");
                NProgress.start();
            }
            else {
                context.commit('ADD_LOADING_COMPONENT');
                //console.log("add loading unit");
            }
        },
        endLoading(context) {
            if(context.getters['howManyComponentsLoading'] <= 1) {
                context.commit('END_LOADING');
                //console.log("everything loaded");
                NProgress.done();
            }
            else {
                context.commit('REMOVE_LOADING_COMPONENT');
                //console.log("unit loaded");
                NProgress.inc();
            }
        },
    }
})
