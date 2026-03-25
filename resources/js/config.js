import runtimeConstants from "@/runtime-constants";

export default {
    features: runtimeConstants.featureFlags ?? {},
    modals: {
        logInModal: "log-in-modal",
        signUpModal: "sign-up-modal",
        forgotPasswordModal: "forgot-password-modal",
        filtersModal: "filters-modal",
        photoGalleryModal: "photo-gallery-modal",
        confirmModal: "confirm-modal"
    },
    ui: {
        countries: runtimeConstants.countries,
        currencies: runtimeConstants.currencies,
        selectedCurrency: runtimeConstants.selectedCurrency,
        languages: runtimeConstants.languages,
        selectedLanguage: runtimeConstants.selectedLanguage,

        countryCurrencyMap: runtimeConstants.countryCurrencyMap,
        sortOptions: runtimeConstants.sortOptions,
        daysOfWeek: runtimeConstants.daysOfWeek,
        bookingTypes: runtimeConstants.bookingTypes,
    }
}
