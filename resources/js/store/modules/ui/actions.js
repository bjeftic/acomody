import apiClient from "@/services/apiClient";

export const setCurrency = async ({}, currencyCode) => {
    try {
        const response = await apiClient.currency.set.post({
            currency: currencyCode,
        });

        if (response.success) {
            window.location.reload();
        }
    } catch (error) {
        console.error("Failed to set currency:", error);
        throw error;
    }
};

export const setLanguage = async ({}, languageCode) => {
    try {
        const response = await apiClient.language.set.post({
            language: languageCode,
        });

        if (response.success) {
            window.location.reload();
        }
    } catch (error) {
        console.error("Failed to set language:", error);
        throw error;
    }
};
