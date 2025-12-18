import apiClient from "@/services/apiClient";

export const setCurrency = async ({}, currencyCode) => {
    try {
        const response = await apiClient.currency.set.post({
            currency: currencyCode
        });

        if (response.success) {
            window.location.reload();
        }
    } catch (error) {
        console.error('Failed to set currency:', error);
        throw error;
    }
};
