import config from "@/config";

export default {
    searchBar: {
        locations: [],
        suggestions: [],
        isLoadingSuggestions: false,
        dateFormat: config.dateFormat,
        results: [],
    },
    accommodations: [],
    totalAccommodationsFound: 0,
    filters: {},
};
