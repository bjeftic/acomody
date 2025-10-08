import "./bootstrap";
import "../css/app.css";
import { createApp } from "vue";
import router from "./router";
import store from "./store";

import { FwbModal, FwbButton, FwbInput, FwbCheckbox } from "flowbite-vue";

import App from "./App.vue";

import BaseWrapper from "@/src/layouts/BaseWrapper.vue";
import FormWrapper from "@/src/layouts/FormWrapper.vue";
import SearchWrapper from "./src/layouts/SearchWrapper.vue";
import ValidationAlertBox from "@/src/components/ValidationAlertBox.vue";

const app = createApp(App);

app.component("FwbModal", FwbModal);
app.component("FwbButton", FwbButton);
app.component("FwbInput", FwbInput);
app.component("FwbCheckbox", FwbCheckbox);
app.component("BaseWrapper", BaseWrapper);
app.component("FormWrapper", FormWrapper);
app.component("SearchWrapper", SearchWrapper);
app.component("ValidationAlertBox", ValidationAlertBox);

app.use(router);
app.use(store);

app.mount("#app");
