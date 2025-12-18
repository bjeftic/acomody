import config from "@/config";

export default {
    languages: [],
    countries: config.ui.countries || [],
    currencies: config.ui.currencies || [],
    selectedCurrency: config.ui.selectedCurrency,
};
