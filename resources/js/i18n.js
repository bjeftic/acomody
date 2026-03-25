import { createI18n } from "vue-i18n";
import runtimeConstants from "@/runtime-constants";

import en from "@/locales/en.yml";
import sr from "@/locales/sr.yml";
import hr from "@/locales/hr.yml";
import mk from "@/locales/mk.yml";
import sl from "@/locales/sl.yml";

const locale = runtimeConstants.selectedLanguage || "en";

const i18n = createI18n({
    legacy: true,
    locale,
    fallbackLocale: "en",
    messages: { en, sr, hr, mk, sl },
});

export default i18n;
