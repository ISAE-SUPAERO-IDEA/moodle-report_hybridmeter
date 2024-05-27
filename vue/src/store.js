/**
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 */

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
        scheduledTime : undefined,
        debug : undefined,
        student_roles : undefined,
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
            };

            state.scheduledTime = {
                scheduled : ((config.has_scheduled_calculation == 0) ? false : true),
                scheduled_timestamp : config.scheduled_date,
            };

            state.debug = config.debug;
            state.student_roles = config.student_roles;
        },
        UPDATE_BLACKLIST(state, blacklistData) {
            state.blacklistData = blacklistData
        },
        UPDATE_SCHEDULED_DATA(state, scheduledTime) {
            state.scheduledTime = scheduledTime
        },
        UPDATE_PROGRAMMED_DATES(state, programmedDates) {
            state.programmedDates = programmedDates
        },
        UPDATE_STUDENT_ROLES(state, student_roles) {
            state.student_roles = student_roles
        },
        UPDATE_DEBUG(state, debug) {
            state.debug = debug
        },
    },
    actions: {
        beginLoading(context) {
            if (!context.getters['isPageLoading']) {
                context.commit('BEGIN_LOADING');
                NProgress.start();
            }
            else {
                context.commit('ADD_LOADING_COMPONENT');
            }
        },
        endLoading(context) {
            if (context.getters['howManyComponentsLoading'] <= 1) {
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
        updateScheduledTimeFromConfig(context, config) {
            let scheduledTime = {
                scheduled : ((config.has_scheduled_calculation == 0) ? false : true),
                scheduled_timestamp : config.scheduled_date,
            };
            context.commit('UPDATE_SCHEDULED_DATA', scheduledTime);
        },
        updateProgrammedDatesFromConfig(context, config) {
            let programmedDates = {
                begin_date : config.begin_date,
                end_date : config.end_date,
            };
            context.commit('UPDATE_PROGRAMMED_DATES', programmedDates);
        },
        updateOtherData(context, config) {
            context.commit('UPDATE_STUDENT_ROLES', config.student_roles);
            context.commit('UPDATE_DEBUG', config.debug);
        },
    }
})
