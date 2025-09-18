import { createApp } from 'vue'
import naive from 'naive-ui'
import { createDiscreteApi } from "naive-ui";
import router from './router';
import store from './store';

import App from './App.vue'

const { message } = createDiscreteApi(["message"]);

import FormWrapper from '@/src/layouts/FormWrapper.vue';
import ValidationAlertBox from '@/src/components/ValidationAlertBox.vue';

import {
    NLayout,
    NLayoutHeader,
    NLayoutContent,
    NForm,
    NFormItem,
    NInput,
    NButton,
    NDropdown,
    NCheckbox,
    NModal,
    NMessageProvider,
    NConfigProvider,
    NSpace,
    NCard,
    NIcon,
    NTag,
    darkTheme,
} from "naive-ui";

const app = createApp(App)

app.component("FormWrapper", FormWrapper);
app.component("ValidationAlertBox", ValidationAlertBox);
app.component("NLayout", NLayout);
app.component("NLayoutHeader", NLayoutHeader);
app.component("NLayoutContent", NLayoutContent);
app.component("NForm", NForm);
app.component("NFormItem", NFormItem);
app.component("NInput", NInput);
app.component("NButton", NButton);
app.component("NCheckbox", NCheckbox);
app.component("NModal", NModal);
app.component("NMessageProvider", NMessageProvider);
app.component("NSpace", NSpace);
app.component("NCard", NCard);
app.component("NIcon", NIcon);
app.component("NTag", NTag);
app.component("NConfigProvider", NConfigProvider);
app.component("NDropdown", NDropdown);

app.use(NConfigProvider, {
    theme: darkTheme,
});

app.config.globalProperties.$message = message;

app.use(naive)
app.use(router);
app.use(store);

app.mount('#app')
