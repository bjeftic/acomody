export const pricesFilter = (state) => {
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
