import apiClient from "@/services/apiClient";
import { sortSearchResults, clone } from "@/utils/helpers";

export const searchLocations = async ({ commit }, query) => {
    try {
        const response = await apiClient.search.locations
            .query({ q: query })
            .get();
        let results = sortSearchResults(response.data || []);
        commit("SET_SEARCH_BAR_SEARCH_RESULTS", results);
        return results;
    } catch (error) {
        console.error("Failed to search locations:", error);
        throw error;
    }
};

export const searchAccommodations = async ({}, searchParams) => {
    return await apiClient.search.accommodations.query(searchParams).get();
};

export const countAccommodations = async ({ dispatch }, searchParams) => {
    try {
        return await dispatch("searchAccommodations", searchParams);
    } catch (error) {
        console.error("Failed to search accommodations:", error);
        throw error;
    }
};

export const getAccommodations = async ({ commit, dispatch }, searchParams) => {
    try {
        const response = await dispatch("searchAccommodations", searchParams);
        commit("SET_ACCOMMODATIONS_SEARCH_RESULTS", response.data);
        commit("SET_FILTERS", {
            filters:
                response.data.facet_counts.find(
                    (stat) => stat.field_name === "price",
                ) || [],
            type: "price",
        });
        commit("SET_TOTAL_ACCOMMODATIONS_FOUND", response.data.found || 0);
        commit("SET_PAGE", response.data.page || 1);
        return response.data.hits;
    } catch (error) {
        console.error("Failed to search accommodations:", error);
        throw error;
    }
};

export const updateFiltersInURL = async (
    { rootState, state, getters },
    { route, router },
) => {
    const query = { ...route.query };

    // Remove all existing price filters first
    Object.keys(query).forEach((key) => {
        if (key.startsWith("price_min_") || key.startsWith("price_max_")) {
            delete query[key];
        }
    });

    const priceMin = state.activeFilters.priceRange?.min;
    const priceMax = state.activeFilters.priceRange?.max;
    const facetMin = getters.accommodationPricesFilters?.stats?.min;
    const facetMax = getters.accommodationPricesFilters?.stats?.max;

    const hasValidMin =
        priceMin !== null && priceMin !== undefined && facetMin !== undefined;
    const hasValidMax =
        priceMax !== null && priceMax !== undefined && facetMax !== undefined;
    const minDifferent = hasValidMin && priceMin !== facetMin;
    const maxDifferent = hasValidMax && priceMax !== facetMax;

    if (hasValidMin && hasValidMax && (minDifferent || maxDifferent)) {
        query["price_min_" + rootState.ui.selectedCurrency.code] =
            String(priceMin);
        query["price_max_" + rootState.ui.selectedCurrency.code] =
            String(priceMax);
    }

    if (state.activeFilters.accommodation_category !== null) {
        query.accommodation_categories =
            state.activeFilters.accommodation_categories.join(",");
    } else {
        delete query.accommodation_categories;
    }

    if (state.activeFilters.accommodation_occupation !== null) {
        query.accommodation_occupations =
            state.activeFilters.accommodation_occupations.join(",");
    } else {
        delete query.accommodation_occupations;
    }

    if (state.activeFilters.amenities?.length) {
        query.amenities = state.activeFilters.amenities.join(",");
    } else {
        delete query.amenities;
    }

    // Clean up null/undefined values
    Object.keys(query).forEach((key) => {
        if (
            query[key] === null ||
            query[key] === undefined ||
            query[key] === ""
        ) {
            delete query[key];
        }
    });

    query.page = String(state.page);

    // Only navigate if query actually changed
    const currentQuery = JSON.stringify(route.query);
    const newQuery = JSON.stringify(query);

    if (currentQuery !== newQuery) {
        await router.replace({ query }).catch((err) => {
            // Ignore navigation duplicated errors
            if (err.name !== "NavigationDuplicated") {
                throw err;
            }
        });
    }
};

export const handleFiltersUpdate = ({ state, commit }, newFilters) => {
    commit("SET_ACTIVE_FILTERS", { ...state.activeFilters, ...newFilters });
};

export const resetPaginationAndSearch = (
    { commit, dispatch },
    { route, router },
) => {
    commit("SET_PAGE", 1);
    dispatch("updatePageInURL", { route, router });
    dispatch("performSearch");
};

export const updatePageInURL = ({ state }, { route, router }) => {
    const query = { ...route.query };
    query.page = state.page;
    router.replace({ query });
};

export const parseURLParams = ({ commit, state, rootState, getters }, query) => {
    let searchParams = clone(state.searchParams);
    let activeFilters = clone(state.activeFilters);

    // Parse page from URL
    if (query.page) {
        commit("SET_PAGE", parseInt(query.page) || 1);
    } else {
        commit("SET_PAGE", 1);
    }

    // Parse bounds from URL
    if (query.ne_lat && query.ne_lng && query.sw_lat && query.sw_lng) {
        commit("SET_CURRENT_MAP_BOUNDS", {
            northEast: {
                lat: parseFloat(query.ne_lat),
                lng: parseFloat(query.ne_lng),
            },
            southWest: {
                lat: parseFloat(query.sw_lat),
                lng: parseFloat(query.sw_lng),
            },
        });
        commit("SET_IS_MAP_SEARCH", true);
    }

    if (query.locationId || query.locationName) {
        searchParams.location = {
            id: query.locationId || null,
            name: query.locationName || "",
        };
        searchParams.locationId = query.locationId || null;
    }

    if (query.checkIn) {
        searchParams.checkIn = query.checkIn;
    }
    if (query.checkOut) {
        searchParams.checkOut = query.checkOut;
    }
    if (query.adults) {
        searchParams.guests.adults = parseInt(query.adults);
    }
    if (query.children) {
        searchParams.guests.children = parseInt(query.children);
    }
    if (query.infants) {
        searchParams.guests.infants = parseInt(query.infants);
    }
    if (query.sort_by) {
        searchParams.sortBy = query.sort_by;
    }

    // we need to commit searchParams here
    commit("SET_SEARCH_PARAMS", searchParams);

    if (query.accommodation_categories) {
        activeFilters.accommodation_categories = query.accommodation_categories.split(",");
    }

    if (query.accommodation_occupations) {
        activeFilters.accommodation_occupations = query.accommodation_occupations.split(",");
    }

    if (query.amenities) {
        activeFilters.amenities = query.amenities.split(",");
    }

    commit("SET_ACTIVE_FILTERS", activeFilters);

    const currentCurrencyMinKey =
        "price_min_" + rootState.ui.selectedCurrency.code;
    const currentCurrencyMaxKey =
        "price_max_" + rootState.ui.selectedCurrency.code;

    const allPriceMinKeys = Object.keys(query).filter((k) =>
        k.startsWith("price_min_"),
    );
    const allPriceMaxKeys = Object.keys(query).filter((k) =>
        k.startsWith("price_max_"),
    );

    const hasDifferentCurrencyPrices =
        allPriceMinKeys.some((k) => k !== currentCurrencyMinKey) ||
        allPriceMaxKeys.some((k) => k !== currentCurrencyMaxKey);

    if (hasDifferentCurrencyPrices) {
        const newQuery = { ...query };

        allPriceMinKeys.forEach((key) => {
            if (key !== currentCurrencyMinKey) {
                delete newQuery[key];
            }
        });

        allPriceMaxKeys.forEach((key) => {
            if (key !== currentCurrencyMaxKey) {
                delete newQuery[key];
            }
        });

        this.$router.replace({ query: newQuery });
    }

    const hasMinInUrl = query[currentCurrencyMinKey] !== undefined;
    const hasMaxInUrl = query[currentCurrencyMaxKey] !== undefined;

    if (hasMinInUrl !== hasMaxInUrl) {
        const newQuery = { ...query };
        delete newQuery[currentCurrencyMinKey];
        delete newQuery[currentCurrencyMaxKey];
        this.$router.replace({ query: newQuery });

        activeFilters.priceRange = {
            min: null,
            max: null,
        };

        commit("SET_ACTIVE_FILTERS", activeFilters);
        return;
    }

    if (hasMinInUrl && hasMaxInUrl) {
        const minValue = parseInt(query[currentCurrencyMinKey]);
        const maxValue = parseInt(query[currentCurrencyMaxKey]);

        if (getters.accommodationPricesFilters?.stats) {
            activeFilters.priceRange = {
                min: Math.max(
                    minValue,
                    getters.accommodationPricesFilters.stats.min,
                ),
                max: Math.min(
                    maxValue,
                    getters.accommodationPricesFilters.stats.max,
                ),
            };
        } else {
            activeFilters.priceRange = {
                min: minValue,
                max: maxValue,
            };
        }
    } else {
        activeFilters.priceRange = {
            min: null,
            max: null,
        };
    }
};

export const performSearch = async ({ commit, rootState, state, dispatch }) => {
    commit("SET_SEARCH_LOADING", true);

    let searchParams = state.searchParams;

    try {
        const filtersToSend = { ...state.activeFilters };
        delete filtersToSend.priceRange;

        const currencyCode = rootState.ui.selectedCurrency.code;
        filtersToSend[`priceRange_${currencyCode}`] = {
            min: state.activeFilters.priceRange?.min ?? null,
            max: state.activeFilters.priceRange?.max ?? null,
        };

        // Build search payload
        const searchPayload = {
            ...searchParams,
            ...filtersToSend,
            page: state.page,
            perPage: 20,
        };

        // Add bounds if doing map search
        if (state.isMapSearch && state.searchParams.bounds) {
            searchPayload.bounds = state.searchParams.bounds;
        }

        const response = await dispatch("getAccommodations", searchPayload);

        const data = response.hits || response.data || response;
        const found = response.found || response.total || 0;

        return {
            data: data || [],
            found,
        };
    } catch (error) {
        console.error("Search error:", error);
        return {
            data: [],
            found: 0,
        };
    } finally {
        commit("SET_SEARCH_LOADING", false);
    }
};

export const handleMapBoundsChanged = (
    { commit, dispatch },
    { route, router, mapBounds },
) => {
    // User interacted with map, so enable map search
    commit("SET_IS_MAP_SEARCH", true);

    // Store current bounds
    commit("SET_CURRENT_MAP_BOUNDS", {
        northEast: mapBounds.northEast,
        southWest: mapBounds.southWest,
    });

    // Reset page to 1 when user interacts with map
    commit("SET_PAGE", 1);

    // Update URL with bounds and page
    dispatch("updateBoundsInURL", { route, router });

    // Search with new bounds
    dispatch("performSearch");
};

export const updateBoundsInURL = ({ state }, { route, router }) => {
    const query = { ...route.query };

    if (state.searchParams.bounds) {
        query.ne_lat = state.searchParams.bounds.northEast.lat.toFixed(6);
        query.ne_lng = state.searchParams.bounds.northEast.lng.toFixed(6);
        query.sw_lat = state.searchParams.bounds.southWest.lat.toFixed(6);
        query.sw_lng = state.searchParams.bounds.southWest.lng.toFixed(6);
    }

    query.page = state.page;

    router.replace({ query });
};

export const updateFetchingPage = (
    { commit, dispatch, state },
    { route, router, newPage },
) => {
    commit("SET_PAGE", newPage);

    // Update URL based on search type
    if (state.isMapSearch) {
        dispatch("updateBoundsInURL", { route, router });
    } else {
        dispatch("updatePageInURL", { route, router });
    }

    dispatch("performSearch");
};

//this method handles search from SearchBar component
export const handleSearch = (
    { commit, dispatch },
    { route, router, searchData },
) => {
    let searchParams = {
        location: searchData.location,
        checkIn: searchData.checkIn,
        checkOut: searchData.checkOut,
        guests: searchData.guests,
        sortBy: searchData.sortBy,
    };

    // Reset map search when doing location search
    commit("SET_IS_MAP_SEARCH", false);
    commit("SET_CURRENT_MAP_BOUNDS", null);

    // Remove bounds from URL
    const query = { ...route.query };
    delete query.ne_lat;
    delete query.ne_lng;
    delete query.sw_lat;
    delete query.sw_lng;
    router.replace({ query });

    commit("SET_SEARCH_PARAMS", searchParams);

    dispatch("updateSearchParamsInURL", { route, router });
    dispatch("resetPaginationAndSearch", { route, router });
};

export const updateSearchParamsInURL = ({ state }, { route, router }) => {
    const query = { ...route.query };

    if (state.searchParams.location.id) {
        query.locationId = state.searchParams.location.id;
    }
    if (state.searchParams.location.name) {
        query.locationName = state.searchParams.location.name;
    }
    if (state.searchParams.checkIn) {
        query.checkIn = state.searchParams.checkIn;
    }
    if (state.searchParams.checkOut) {
        query.checkOut = state.searchParams.checkOut;
    }

    query.adults = state.searchParams.guests.adults || 2;
    query.children = state.searchParams.guests.children || 0;
    query.infants = state.searchParams.guests.infants || 0;
    query.sort_by = state.searchParams.sortBy || "recommended";
    query.page = state.page;

    router.replace({ query });
};

export const getFilters = async ({ commit }) => {
    const filters = await apiClient.public.filters.get();
    commit("SET_FILTERS", { filters: filters.data, type: "highlighted" });
};

export const handleSortChange = (
    { commit, dispatch },
    { route, router, newSortBy },
) => {
    commit("SET_SORT_BY", newSortBy);
    commit("SET_PAGE", 1);
    dispatch("updateSearchParamsInURL", { route, router });
    dispatch("performSearch");
};
