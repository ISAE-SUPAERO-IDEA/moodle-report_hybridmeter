import Vuex from 'vuex'
import NProgress from 'nprogress'
import 'nprogress/nprogress.css'

const LoadingStatus = {
    NotLoading: 0,
    Loading: 1,
}

export default new Vuex.Store({
    state: {
        componentsLoadingStatus : 0,
        pageLoadingStatus : LoadingStatus.NotLoading,
        programmedDates : undefined,
        blacklistData : undefined,
        scheduledData : undefined,
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
        LOAD_CONFIG(state, config) {
            state.programmedDates = {
                begin_date : config.begin_date,
                end_date : config.end_date,
            };

            state.blacklistData = {
                blacklisted_courses : config.blacklisted_courses,
                blacklisted_categories : config.blacklisted_categories,
                save_blacklist_courses : config.save_blacklist_courses,
                save_blacklist_categories : config.save_blacklist_categories,
            }
        },
        UPDATE_BLACKLIST(state, blacklistData) {
            state.blacklistData = blacklistData
        },
        UPDATE_SCHEDULED_DATA(state, scheduledData) {
            state.scheduledData = scheduledData
        },
        UPDATE_PROGRAMMED_DATES(state, programmedDates) {
            state.programmedDates = programmedDates
        }
    },
    actions: {
        beginLoading(context) {
            if(!context.getters['isPageLoading']) {
                context.commit('BEGIN_LOADING');
                NProgress.start();
            }
            else {
                context.commit('ADD_LOADING_COMPONENT');
            }
        },
        endLoading(context) {
            if(context.getters['howManyComponentsLoading'] <= 1) {
                context.commit('END_LOADING');
                NProgress.done();
            }
            else {
                context.commit('REMOVE_LOADING_COMPONENT');
                NProgress.inc();
            }
        },
        loadConfig(context, config) {
            context.commit('LOAD_CONFIG', config);
        },
        updateBlacklistFromConfig(context, config) {
            let blacklistData = {
                blacklisted_courses : config.blacklisted_courses,
                blacklisted_categories : config.blacklisted_categories,
                save_blacklist_courses : config.save_blacklist_courses,
                save_blacklist_categories : config.save_blacklist_categories,
            };
            context.commit('UPDATE_BLACKLIST', blacklistData);
        },
        updateScheduledDataFromConfig(context, config) {
            let scheduledData = {
                has_scheduled_calculation : config.has_scheduled_calculation,
                scheduled_date : config.scheduled_date,
            };
            context.commit('UPDATE_SCHEDULED_DATA', scheduledData);
        },
        updateProgrammedDatesFromConfig(context, config) {
            let programmedDates = {
                begin_date : config.begin_date,
                end_date : config.end_date,
            };
            context.commit('UPDATE_PROGRAMMED_DATES', programmedDates);
        },
    }
})
