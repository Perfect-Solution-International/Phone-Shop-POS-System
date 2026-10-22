import Vue from 'vue';
import './plugins';
import store from './store';
import Login from './views/Login.vue';

new Vue({ store, render: (h) => h(Login) }).$mount('#login');
