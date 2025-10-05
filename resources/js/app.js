import './bootstrap';
import '../css/app.css';
import { createApp } from 'vue'
import router from './router';
import store from './store';

import App from './App.vue'

import FormWrapper from '@/src/layouts/FormWrapper.vue';
import ValidationAlertBox from '@/src/components/ValidationAlertBox.vue';

const app = createApp(App)

app.component("FormWrapper", FormWrapper);
app.component("ValidationAlertBox", ValidationAlertBox);

app.use(router);
app.use(store);

app.mount('#app')
