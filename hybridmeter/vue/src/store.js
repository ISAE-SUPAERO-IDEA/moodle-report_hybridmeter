/*
 * Hybrid Meter
 * Copyright (C) 2020 - 2024  ISAE-SUPAERO
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
        student_archetype : undefined,
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
            state.student_archetype = config.student_archetype;
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
        UPDATE_STUDENT_ARCHETYPE(state, student_archetype) {
            state.student_archetype = student_archetype
        },
        UPDATE_DEBUG(state, debug) {
            state.debug = debug
        },
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
            context.commit('UPDATE_STUDENT_ARCHETYPE', config.student_archetype);
            context.commit('UPDATE_DEBUG', config.debug);
        },
    }
})
