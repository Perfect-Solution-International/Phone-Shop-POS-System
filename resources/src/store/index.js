import Vue from 'vue';
import Vuex from 'vuex';
import auth from './modules/auth';
import employees from './modules/employees';
import attendance from './modules/attendance';
import leave from './modules/leave';
import payroll from './modules/payroll';
import compactSidebar from './modules/compactSidebar';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: { auth, employees, attendance, leave, payroll, compactSidebar },
});
