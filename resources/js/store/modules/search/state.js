import config from "@/config";

export default {
    searchBar: {
        locations: [],
        suggestions: [],
        isLoadingSuggestions: false,
        dateFormat: config.dateFormat,
        results: [],
    },
    isMapSearch: false,
    accommodations: [],
    accommodationsOnMap: [],
    totalAccommodationsFound: 0,
    filters: {},
};
