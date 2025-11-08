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
        const { data } = await apiClient.accommodationDrafts.get();

        commit("SET_ACCOMMODATION_DRAFT_ID", data.id);
        commit("SET_ACCOMMODATION_DRAFT", data);
        commit("SET_CREATE_ACCOMMODATION_STEP", data.current_step);

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

export const updateAccommodationDraft = async (
    { commit },
    { status, draftData, currentStep }
) => {
    try {
        const response = await apiClient.accommodationDrafts.save.post({
            status: status,
            data: draftData,
            current_step: currentStep,
        });

        commit("SET_ACCOMMODATION_DRAFT", response.data);

        return response;
    } catch (error) {
        throw error;
    }
};

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
 * Update photo details
 */
export const updatePhoto = async ({}, { draftId, photoId, data }) => {
    try {
        const response = await apiClient.accommodationDrafts[draftId].photos[
            photoId
        ].put(data);

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

/**
 * Delete all photos for a draft
 */
export const deleteAllPhotos = async ({}, draftId) => {
    try {
        const response = await apiClient.accommodationDrafts[
            draftId
        ].photos.delete();

        return response.data;
    } catch (error) {
        throw error;
    }
};
