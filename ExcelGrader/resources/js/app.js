require('./bootstrap');
import { createApp } from 'vue'
import vClickOutside from "click-outside-vue3"
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import Toast from 'primevue/toast';
import Tooltip from 'primevue/tooltip';
import Auth from '../api/utils/auth';

//theme
import "primevue/resources/themes/lara-light-indigo/theme.css";

//core
import "primevue/resources/primevue.min.css";
//icons
import "primeicons/primeicons.css";
import VComponent from './base/base-component/base-component'
import ConfirmDialog from 'primevue/confirmdialog';
import Button from 'primevue/button';
import FormMode from './base/base-enum/form-mode';
import KeyCode from './base/base-enum/key-code';
import App from './App.vue'
import router from './router/router'
import store from '../store';
// import i18n from '../i18n/i18n';
const app = createApp(App)
export function handleServerError() {
    app.config.globalProperties.$toast.add({ severity: 'error', summary: 'Thông báo', detail: 'Có lỗi xảy ra, vui lòng liên hệ nhà phát triển.', life: 3000 });
};

export function handleSuccess(message) {
    app.config.globalProperties.$toast.add({ severity: 'success', summary: 'Thông báo', detail: message, life: 3000 });
};

app.config.globalProperties.auth = Auth;
app.config.globalProperties.FormMode = FormMode;
app.config.globalProperties.KeyCode = KeyCode;
app.directive('tooltip', Tooltip);
app.component('VComponent', VComponent)
app.component('Button', Button)
app.component('ConfirmDialog', ConfirmDialog)
app.component('Toast', Toast);
// app.use(i18n)
app.use(PrimeVue)
app.use(ToastService);
app.use(ConfirmationService);
app.use(store)
app.use(vClickOutside)
app.use(router)
app.mount('#app')
