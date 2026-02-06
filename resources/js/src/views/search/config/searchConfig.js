// Search Configuration
export const searchConfig = {
    // Map configuration
    map: {
        defaultCenter: { lat: 47.4979, lng: 19.0402 }, // Budapest
        defaultZoom: 12,
        minZoom: 3,
        maxZoom: 18,
        clusterRadius: 60, // pixels
        markerSize: { width: 40, height: 40 },
    },

    // Grid layout
    grid: {
        desktop: 3, // columns
        tablet: 2,
        mobile: 1,
        gap: "24px",
    },

    // Price range
    priceRange: {
        min: 0,
        max: 1000,
        step: 10,
        currency: "$",
    },

    // Guest limits
    guestLimits: {
        adults: { min: 1, max: 16 },
        children: { min: 0, max: 15 },
        infants: { min: 0, max: 5 },
        pets: { min: 0, max: 5 },
    },

    // Search radius (km)
    searchRadius: {
        default: 15,
        options: [5, 10, 15, 25, 50, 100],
    },

    // Flexible dates options
    flexibleDates: {
        enabled: true,
        options: [
            { id: "exact", name: "Exact dates" },
            { id: "plus_minus_1", name: "± 1 day" },
            { id: "plus_minus_3", name: "± 3 days" },
            { id: "plus_minus_7", name: "± 7 days" },
            { id: "weekend", name: "Weekends only" },
            { id: "month", name: "Anytime in month" },
        ],
    },

    // Instant book badge
    instantBook: {
        enabled: true,
        icon: "⚡",
        label: "Instant Book",
    },

    // Superhost badge
    superhost: {
        enabled: true,
        icon: "★",
        label: "Superhost",
    },

    // Image carousel
    imageCarousel: {
        showArrows: true,
        showDots: true,
        autoplay: false,
        maxImages: 10,
    },

    // Skeleton loaders
    skeleton: {
        cardCount: 9,
        shimmer: true,
    },

    // Infinite scroll
    infiniteScroll: {
        enabled: true,
        threshold: 300, // pixels from bottom
    },

    // API endpoints
    api: {
        search: "/api/accommodations/search",
        autocomplete: "/api/locations/autocomplete",
        suggestions: "/api/locations/suggestions",
    },

    // Debounce times (ms)
    debounce: {
        search: 500,
        filters: 300,
        map: 200,
    },

    // Local storage keys
    storage: {
        recentSearches: "accommodation_recent_searches",
        savedFilters: "accommodation_saved_filters",
        viewPreference: "accommodation_view_preference",
    },

    // Featured destinations
    featuredDestinations: [
        {
            id: "budapest",
            name: "Budapest",
            country: "Hungary",
            image: "/images/destinations/budapest.jpg",
            popular: true,
        },
        {
            id: "prague",
            name: "Prague",
            country: "Czech Republic",
            image: "/images/destinations/prague.jpg",
            popular: true,
        },
        {
            id: "vienna",
            name: "Vienna",
            country: "Austria",
            image: "/images/destinations/vienna.jpg",
            popular: true,
        },
        {
            id: "paris",
            name: "Paris",
            country: "France",
            image: "/images/destinations/paris.jpg",
            popular: true,
        },
    ],

    // Search suggestions
    searchSuggestions: {
        showRecent: true,
        showPopular: true,
        maxRecent: 5,
        maxPopular: 8,
    },

    // Mobile breakpoints
    breakpoints: {
        mobile: 640,
        tablet: 768,
        desktop: 1024,
        wide: 1280,
    },

    // Animation durations (ms)
    animations: {
        fade: 200,
        slide: 300,
        bounce: 400,
    },
};
