import apiClient from "@/services/apiClient";

export const loadInitialCreateAccommodationData = async ({ dispatch }) => {
    const actions = [
        dispatch("fetchAccommodationDraft"),
        dispatch("fetchAccommodationTypes"),
        dispatch("fetchAmenities"),
    ];

    await Promise.all(actions).finally(() => {
        dispatch("setCreateAccommodationLoading", false);
    });
};

export const fetchAccommodationDraft = async ({ commit }) => {
    try {
        const { data } = await apiClient.accommodationDrafts.draft
            .get();

        commit("SET_ACCOMMODATION_DRAFT_ID", data);
        commit("SET_ACCOMMODATION_DRAFT", data);
        commit("SET_CREATE_ACCOMMODATION_STEP", data);

        return data;
    } catch (error) {
        console.error("Failed to fetch accommodation draft:", error);
        throw error;
    }
};

export const fetchAccommodationTypes = async ({ commit }) => {
    try {
        const response = await apiClient.accommodationTypes.get();

        commit("SET_ACCOMMODATION_TYPES", response.data);

        return response;
    } catch (error) {
        throw error;
    }
};

export const fetchAmenities = async ({ commit }) => {
    const response = await apiClient.amenities.get();

    commit("SET_AMENITIES", response.data);
    return response;
};

export const incrementCurrentStep = ({ commit }) => {
    commit("INCREMENT_CURRENT_STEP");
};

export const decrementCurrentStep = ({ commit }) => {
    commit("DECREMENT_CURRENT_STEP");
};

export const createAccommodationDraft = async (
    { commit },
    { draftData }
) => {
    try {
        const response = await apiClient.accommodationDrafts.post({
            data: draftData,
        });

        commit("SET_ACCOMMODATION_DRAFT_ID", response.data);
        commit("SET_ACCOMMODATION_DRAFT", response.data);
        commit("SET_CREATE_ACCOMMODATION_STEP", response.data);

        return response;
    } catch (error) {
        throw error;
    }
}

export const updateAccommodationDraft = async (
    { commit },
    { draftId, status, draftData, currentStep }
) => {
    try {
        const response = await apiClient.accommodationDrafts[draftId].put({
            status: status,
            data: draftData,
            current_step: currentStep,
        });

        commit("SET_ACCOMMODATION_DRAFT_ID", response.data);
        commit("SET_ACCOMMODATION_DRAFT", response.data);
        commit("SET_CREATE_ACCOMMODATION_STEP", response.data);

        return response;
    } catch (error) {
        throw error;
    }
};

export const restartAccommodationDraftData = ({ commit }) => {
    commit("RESET_ACCOMMODATION_DRAFT");
}

export const setCreateAccommodationLoading = ({ commit }, isLoading) => {
    commit("SET_CREATE_ACCOMMODATION_LOADING", isLoading);
};

export const goToStep = ({ commit }, step) => {
    commit("SET_CREATE_ACCOMMODATION_STEP", step);
};

/**
 * Photos
 */

export const uploadPhotos = async (
    {},
    { draftId, files, onProgress = null }
) => {
    try {
        const response = await apiClient.accommodationDrafts[
            draftId
        ].photos.upload(files, "photos", onProgress);

        return response.data;
    } catch (error) {
        console.error("Failed to upload photos:", error);
        throw error;
    }
};

/**
 * Upload single photo
 */
export const uploadPhoto = async (
    { dispatch },
    { draftId, file, onProgress = null }
) => {
    return dispatch("uploadPhotos", { draftId, files: [file], onProgress });
};

/**
 * Fetch all photos for a draft
 */
export const fetchPhotos = async ({ commit }, draftId) => {
    try {
        const response = await apiClient.accommodationDrafts[
            draftId
        ].photos.get();

        commit("SET_ACCOMMODATION_DRAFT_PHOTOS", response.data);

        return response.data;
    } catch (error) {
        throw error;
    }
};

/**
 * Reorder photos
 */
export const reorderPhotos = async ({}, { draftId, photoIds }) => {
    try {
        const response = await apiClient.accommodationDrafts[
            draftId
        ].photos.reorder.put({
            photo_ids: photoIds,
        });

        return response.data;
    } catch (error) {
        throw error;
    }
};

/**
 * Delete a single photo
 */
export const deletePhoto = async ({ commit }, { draftId, photoId }) => {
    try {
        const response = await apiClient.accommodationDrafts[draftId].photos[
            photoId
        ].delete();

        commit("REMOVE_ACCOMMODATION_DRAFT_PHOTO", photoId);

        return response.data;
    } catch (error) {
        throw error;
    }
};
