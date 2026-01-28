export const accommodationPricesFilters = (state) => {
    if (!state.filters || !Array.isArray(state.filters)) {
        return [];
    }
    const regularPrice = state.filters.find((price) => price.field_name === "price");
    if (regularPrice) {
        return regularPrice;
    } else {
        return [];
    }
};

export const accommodationFilters = (state) => {
    if (!state.filters || !Array.isArray(state.filters)) {
        return [];
    }
    return state.filters.filter((filter) => filter.field_name !== "price");
};
