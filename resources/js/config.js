import runtimeConstants from "@/runtime-constants";

export default {
    modals: {
        logInModal: "log-in-modal",
        signUpModal: "sign-up-modal",
        forgotPasswordModal: "forgot-password-modal",
    },
    ui: {
        countries: runtimeConstants.countries,
        currencies: runtimeConstants.currencies,
        daysOfWeek: [
            { id: "monday", name: "Monday" },
            { id: "tuesday", name: "Tuesday" },
            { id: "wednesday", name: "Wednesday" },
            { id: "thursday", name: "Thursday" },
            { id: "friday", name: "Friday" },
            { id: "saturday", name: "Saturday" },
            { id: "sunday", name: "Sunday" }
        ],
        currency: "RSD",
    }
}
