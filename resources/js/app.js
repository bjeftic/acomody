import "./bootstrap";
import "../css/app.css";
import { createApp } from "vue";
import router from "./router";
import store from "./store";

import {
    FwbAlert,
    FwbAccordion,
    FwbAccordionPanel,
    FwbAccordionHeader,
    FwbAccordionContent,
    FwbModal,
    FwbButton,
    FwbCard,
    FwbInput,
    FwbCheckbox,
    FwbRating,
    FwbSpinner,
    FwbFooter,
    FwbFooterCopyright,
    FwbFooterLink,
    FwbFooterLinkGroup,
} from "flowbite-vue";

import App from "./App.vue";

import BaseWrapper from "@/src/layouts/BaseWrapper.vue";
import FormWrapper from "@/src/layouts/FormWrapper.vue";
import SearchWrapper from "./src/layouts/SearchWrapper.vue";
import ValidationAlertBox from "@/src/components/ValidationAlertBox.vue";

const app = createApp(App);

app.component("FwbAlert", FwbAlert);
app.component("FwbAccordion", FwbAccordion);
app.component("FwbAccordionPanel", FwbAccordionPanel);
app.component("FwbAccordionHeader", FwbAccordionHeader);
app.component("FwbAccordionContent", FwbAccordionContent);
app.component("FwbModal", FwbModal);
app.component("FwbButton", FwbButton);
app.component("FwbCard", FwbCard);
app.component("FwbInput", FwbInput);
app.component("FwbCheckbox", FwbCheckbox);
app.component("FwbRating", FwbRating);
app.component("FwbSpinner", FwbSpinner);
app.component("FwbFooter", FwbFooter);
app.component("FwbFooterCopyright", FwbFooterCopyright);
app.component("FwbFooterLink", FwbFooterLink);
app.component("FwbFooterLinkGroup", FwbFooterLinkGroup);
app.component("BaseWrapper", BaseWrapper);
app.component("FormWrapper", FormWrapper);
app.component("SearchWrapper", SearchWrapper);
app.component("ValidationAlertBox", ValidationAlertBox);

app.use(router);
app.use(store);

app.mount("#app");
