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
    loading: false,
    accommodations: [],
    totalAccommodationsFound: 0,
    filters: {
        price: [],
        highlighted: []
    },
    activeFilters: {
        priceRange: { min: null, max: null },
        accommodation_categories: [],
        accommodation_occupations: [],
        amenities: [],
        bedrooms: { min: 0, max: null },
        beds: { min: 0, max: null },
        bathrooms: { min: 0, max: null },
        bookingOptions: [],
        hostLanguages: [],
    },
    searchParams: {
        location: "",
        checkIn: null,
        checkOut: null,
        guests: {
            adults: 1,
            children: 0,
            infants: 0,
            pets: 0,
        },
        bounds: null,
        sortBy: "price_asc",
        flexibleDates: false,
    },
    page: 1,
};
