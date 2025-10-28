import apiClient from "@/services/apiClient";

export const fetchAccommodationTypes = async ({ commit }) => {
    try {
        const response = await apiClient.accommodationTypes.get();

        commit("SET_ACCOMMODATION_TYPES", response.data);

        return response;
    } catch (error) {
        throw error;
    }
};

export const incrementCurrentStep = ({ commit }) => {
    commit("INCREMENT_CURRENT_STEP");
};

export const decrementCurrentStep = ({ commit }) => {
    commit("DECREMENT_CURRENT_STEP");
};

export const updateAccommodationDraft = async ({ commit }, { draftData, currentStep }) => {
    try {
        const response = await apiClient.accommodationDrafts.save.post({
            data: draftData,
            current_step: currentStep,
        });

        commit("hosting/SET_ACCOMMODATION_DRAFT", response.data, { root: true });

        return response;
    } catch (error) {
        throw error;
    }
};
