import * as types from "./mutation-types";

export default {
    [types.SET_ACCOMMODATION_DRAFT_ID](state, accommodationDraft) {
        state.accommodationDraftId = accommodationDraft.data.id;
    },
    [types.SET_ACCOMMODATION_DRAFT](state, accommodationDraft) {
        state.accommodationDraft = accommodationDraft.data.data;
    },
    [types.SET_ACCOMMODATION_DRAFT_PHOTOS](state, photos) {
        state.accommodationDraft.photos = photos.data;
    },
    [types.REMOVE_ACCOMMODATION_DRAFT_PHOTO](state, photoId) {
        state.accommodationDraft.photos = state.accommodationDraft.photos.filter(
            (photo) => photo.id !== photoId
        );
    },
    [types.SET_ACCOMMODATION_TYPES](state, accommodationTypes) {
        state.accommodationTypes = accommodationTypes.data;
    },
    [types.SET_AMENITIES](state, amenities) {
        state.amenities = amenities.data;
    },
    [types.SET_CREATE_ACCOMMODATION_STEP](state, accommodationDraft) {
        state.currentStep = accommodationDraft.data.current_step;
    },
    [types.INCREMENT_CURRENT_STEP](state) {
        state.currentStep += 1;
    },
    [types.DECREMENT_CURRENT_STEP](state) {
        state.currentStep -= 1;
    },
    [types.SET_CREATE_ACCOMMODATION_LOADING](state, isLoading) {
        state.createAccommodationLoading = isLoading;
    },
};
