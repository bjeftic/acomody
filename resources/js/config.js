import runtimeConstants from "@/runtime-constants";

export default {
    modals: {
        logInModal: "log-in-modal",
        signUpModal: "sign-up-modal",
        forgotPasswordModal: "forgot-password-modal",
        filtersModal: "filters-modal",
    },
    ui: {
        countries: runtimeConstants.countries,
        currencies: runtimeConstants.currencies,
        selectedCurrency: runtimeConstants.selectedCurrency,

        countryCurrencyMap: runtimeConstants.countryCurrencyMap,
        sortOptions: runtimeConstants.sortOptions,
        daysOfWeek: runtimeConstants.daysOfWeek,
    }
}
