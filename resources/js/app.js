import "./bootstrap";
import "../css/app.css";
import { createApp } from "vue";
import router from "./router";
import store from "./store";

//Flowbite Vue Components
import {
    FwbAlert,
    FwbAccordion,
    FwbAccordionPanel,
    FwbAccordionHeader,
    FwbAccordionContent,
    FwbModal,
    FwbBadge,
    FwbButton,
    FwbCard,
    FwbInput,
    FwbCheckbox,
    FwbDropdown,
    FwbListGroup,
    FwbListGroupItem,
    FwbNavbar,
    FwbPagination,
    FwbRating,
    FwbSelect,
    FwbSpinner,
    FwbFooter,
    FwbFooterBrand,
    FwbFooterCopyright,
    FwbFooterIcon,
    FwbFooterLink,
    FwbFooterLinkGroup,
    FwbTooltip,
    FwbTextarea,
} from "flowbite-vue";

import App from "./App.vue";

//Wrappers
import BaseWrapper from "@/src/layouts/BaseWrapper.vue";
import FormWrapper from "@/src/layouts/FormWrapper.vue";
import SearchWrapper from "./src/layouts/SearchWrapper.vue";

//Custom Components
import ValidationAlertBox from "@/src/components/ValidationAlertBox.vue";

//Common Components
import ActionCard from "@/src/components/common/ActionCard.vue";
import SelectActionCard from "@/src/components/common/SelectActionCard.vue";
import FormSkeleton from "@/src/components/common/skeletons/FormSkeleton.vue";
import FilterSkeleton from "@/src/components/common/skeletons/FilterSkeleton.vue";
import MainSkeleton from "@/src/components/common/skeletons/MainSkeleton.vue";

// Lucide icons loader
import IconLoader from "@/src/components/IconLoader.vue";

const app = createApp(App);

app.component("FwbAlert", FwbAlert);
app.component("FwbAccordion", FwbAccordion);
app.component("FwbAccordionPanel", FwbAccordionPanel);
app.component("FwbAccordionHeader", FwbAccordionHeader);
app.component("FwbAccordionContent", FwbAccordionContent);
app.component("FwbModal", FwbModal);
app.component("FwbBadge", FwbBadge);
app.component("FwbButton", FwbButton);
app.component("FwbCard", FwbCard);
app.component("FwbInput", FwbInput);
app.component("FwbCheckbox", FwbCheckbox);
app.component("FwbDropdown", FwbDropdown);
app.component("FwbListGroup", FwbListGroup);
app.component("FwbListGroupItem", FwbListGroupItem);
app.component("FwbNavbar", FwbNavbar);
app.component("FwbPagination", FwbPagination);
app.component("FwbRating", FwbRating);
app.component("FwbSelect", FwbSelect);
app.component("FwbSpinner", FwbSpinner);
app.component("FwbFooter", FwbFooter);
app.component("FwbFooterBrand", FwbFooterBrand);
app.component("FwbFooterCopyright", FwbFooterCopyright);
app.component("FwbFooterIcon", FwbFooterIcon);
app.component("FwbFooterLink", FwbFooterLink);
app.component("FwbFooterLinkGroup", FwbFooterLinkGroup);
app.component("FwbTooltip", FwbTooltip);
app.component("FwbTextarea", FwbTextarea);

//Wrappers
app.component("BaseWrapper", BaseWrapper);
app.component("FormWrapper", FormWrapper);
app.component("SearchWrapper", SearchWrapper);

//Custom Components
app.component("ValidationAlertBox", ValidationAlertBox);

//Common Components
app.component("ActionCard", ActionCard);
app.component("SelectActionCard", SelectActionCard);
app.component("FormSkeleton", FormSkeleton);
app.component("FilterSkeleton", FilterSkeleton);
app.component("MainSkeleton", MainSkeleton);

app.component("IconLoader", IconLoader)

store.dispatch("auth/initializeAuth").finally(() => {
    app.use(store);
    app.use(router);
    app.mount("#app");
});
