import Vue from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import VueSweetalert2 from 'vue-sweetalert2';
import VueMeta from 'vue-meta';
import { ValidationObserver, ValidationProvider, extend, localize } from 'vee-validate';
import { required, email, min, max, numeric, regex } from 'vee-validate/dist/rules';
import english from 'vee-validate/dist/locale/en.json';

Vue.use(BootstrapVue);
Vue.use(VueSweetalert2);
Vue.use(VueMeta);
Vue.component('ValidationObserver', ValidationObserver);
Vue.component('ValidationProvider', ValidationProvider);
Object.entries({ required, email, min, max, numeric, regex }).forEach(([name, rule]) => extend(name, rule));
localize('en', english);
Vue.config.productionTip = false;
