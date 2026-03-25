import config from "@/config";

export default {
    languages: config.ui.languages || [],
    selectedLanguage: config.ui.selectedLanguage || 'en',
    countries: config.ui.countries || [],
    currencies: config.ui.currencies || [],
    selectedCurrency: config.ui.selectedCurrency,
};
