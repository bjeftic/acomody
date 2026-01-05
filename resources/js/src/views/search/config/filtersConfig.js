// Filters Configuration
export const filtersConfig = {
    // Property types
    propertyTypes: [
        { id: "house", name: "House", icon: "🏠", popular: true },
        { id: "apartment", name: "Apartment", icon: "🏢", popular: true },
        { id: "villa", name: "Villa", icon: "🏰", popular: true },
        { id: "cabin", name: "Cabin", icon: "🏕️", popular: false },
        { id: "cottage", name: "Cottage", icon: "🏡", popular: false },
        { id: "loft", name: "Loft", icon: "🏭", popular: false },
        { id: "townhouse", name: "Townhouse", icon: "🏘️", popular: false },
        { id: "bungalow", name: "Bungalow", icon: "🛖", popular: false },
    ],

    // Room types (property access)
    roomTypes: [
        {
            id: "entire_place",
            name: "Entire place",
            description: "Guests have the whole place to themselves",
            icon: "🏠",
        },
        {
            id: "private_room",
            name: "Private room",
            description: "Guests have their own room in a shared space",
            icon: "🚪",
        },
        {
            id: "shared_room",
            name: "Shared room",
            description: "Guests sleep in a room with others",
            icon: "🛏️",
        },
    ],

    // Amenities (categorized)
    amenities: {
        essentials: [
            { id: "wifi", name: "WiFi", icon: "📶", popular: true },
            { id: "kitchen", name: "Kitchen", icon: "🍳", popular: true },
            { id: "washer", name: "Washer", icon: "🧺", popular: true },
            { id: "dryer", name: "Dryer", icon: "👕", popular: false },
            { id: "ac", name: "Air conditioning", icon: "❄️", popular: true },
            { id: "heating", name: "Heating", icon: "🔥", popular: true },
        ],
        facilities: [
            { id: "pool", name: "Pool", icon: "🏊", popular: true },
            { id: "hot_tub", name: "Hot tub", icon: "🛁", popular: true },
            { id: "gym", name: "Gym", icon: "💪", popular: false },
            { id: "bbq", name: "BBQ grill", icon: "🍖", popular: false },
            { id: "outdoor_dining", name: "Outdoor dining", icon: "🪑", popular: false },
            { id: "fire_pit", name: "Fire pit", icon: "🔥", popular: false },
        ],
        location: [
            { id: "beachfront", name: "Beachfront", icon: "🏖️", popular: true },
            { id: "waterfront", name: "Waterfront", icon: "🌊", popular: false },
            { id: "ski_in_out", name: "Ski-in/Ski-out", icon: "⛷️", popular: false },
        ],
        safety: [
            { id: "smoke_alarm", name: "Smoke alarm", icon: "🚨", popular: false },
            { id: "carbon_monoxide", name: "Carbon monoxide alarm", icon: "☁️", popular: false },
            { id: "first_aid", name: "First aid kit", icon: "🩹", popular: false },
            { id: "fire_extinguisher", name: "Fire extinguisher", icon: "🧯", popular: false },
        ],
    },

    // Booking options
    bookingOptions: [
        {
            id: "instant_book",
            name: "Instant Book",
            description: "Book without waiting for host approval",
            icon: "⚡",
            popular: true,
        },
        {
            id: "self_checkin",
            name: "Self check-in",
            description: "Easy access with keypad or lockbox",
            icon: "🔑",
            popular: true,
        },
        {
            id: "allows_pets",
            name: "Pets allowed",
            description: "Bring your furry friends",
            icon: "🐾",
            popular: false,
        },
    ],

    // Top tier stays
    topTierStays: [
        {
            id: "superhost",
            name: "Superhost",
            description: "Experienced hosts with great reviews",
            icon: "⭐",
        },
        {
            id: "plus",
            name: "Airbnb Plus",
            description: "Verified quality homes",
            icon: "✨",
        },
    ],

    // Host languages
    hostLanguages: [
        { id: "en", name: "English", flag: "🇬🇧" },
        { id: "es", name: "Spanish", flag: "🇪🇸" },
        { id: "fr", name: "French", flag: "🇫🇷" },
        { id: "de", name: "German", flag: "🇩🇪" },
        { id: "it", name: "Italian", flag: "🇮🇹" },
        { id: "pt", name: "Portuguese", flag: "🇵🇹" },
        { id: "ru", name: "Russian", flag: "🇷🇺" },
        { id: "zh", name: "Chinese", flag: "🇨🇳" },
        { id: "ja", name: "Japanese", flag: "🇯🇵" },
        { id: "ko", name: "Korean", flag: "🇰🇷" },
    ],

    // Accessibility features
    accessibility: [
        {
            id: "step_free_entrance",
            name: "Step-free guest entrance",
            icon: "♿",
        },
        {
            id: "step_free_bedroom",
            name: "Step-free bedroom access",
            icon: "🛏️",
        },
        {
            id: "step_free_bathroom",
            name: "Step-free bathroom access",
            icon: "🚿",
        },
        {
            id: "wide_doorways",
            name: "Wide doorways (32+ inches)",
            icon: "🚪",
        },
        {
            id: "accessible_parking",
            name: "Accessible parking spot",
            icon: "🅿️",
        },
        {
            id: "elevator",
            name: "Elevator",
            icon: "🛗",
        },
    ],

    // House rules
    houseRules: [
        { id: "smoking_allowed", name: "Smoking allowed", icon: "🚬" },
        { id: "events_allowed", name: "Events allowed", icon: "🎉" },
        { id: "pets_allowed", name: "Pets allowed", icon: "🐾" },
        { id: "children_welcome", name: "Suitable for children", icon: "👶" },
        { id: "infants_welcome", name: "Suitable for infants", icon: "🍼" },
    ],

    // Cancellation policies
    cancellationPolicies: [
        { id: "flexible", name: "Flexible", description: "Cancel up to 24 hours before" },
        { id: "moderate", name: "Moderate", description: "Cancel up to 5 days before" },
        { id: "firm", name: "Firm", description: "Cancel up to 30 days before" },
        { id: "strict", name: "Strict", description: "Cancel up to 7 days before" },
    ],

    // Filter groups for "More filters" modal
    filterGroups: [
        {
            id: "property_type",
            name: "Property type",
            icon: "🏠",
            filters: "propertyTypes",
        },
        {
            id: "rooms_beds",
            name: "Rooms and beds",
            icon: "🛏️",
            filters: "roomsAndBeds",
        },
        {
            id: "amenities",
            name: "Amenities",
            icon: "✨",
            filters: "amenities",
        },
        {
            id: "booking_options",
            name: "Booking options",
            icon: "⚡",
            filters: "bookingOptions",
        },
        {
            id: "accessibility",
            name: "Accessibility",
            icon: "♿",
            filters: "accessibility",
        },
        {
            id: "host_language",
            name: "Host language",
            icon: "💬",
            filters: "hostLanguages",
        },
    ],

    // Default filter values
    defaults: {
        priceRange: { min: 0, max: 1000 },
        propertyTypes: [],
        roomTypes: [],
        amenities: [],
        bedrooms: { min: 0, max: null },
        beds: { min: 0, max: null },
        bathrooms: { min: 0, max: null },
        bookingOptions: [],
        instantBook: false,
        selfCheckIn: false,
        superhost: false,
        hostLanguages: [],
        accessibility: [],
        houseRules: [],
        cancellationPolicy: null,
    },
};
