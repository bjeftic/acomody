// Pricing Configuration
export const pricingConfig = {
    // Currency settings
    currency: "$",
    currencyCode: "USD",

    // Service fees
    guestServiceFeePercentage: 14, // 14% guest service fee
    hostServiceFeePercentage: 3, // 3% host service fee

    // Competitive pricing (mockup data - should come from API)
    // These values should be dynamically fetched based on location
    averagePrice: 75,
    priceRange: {
        min: 40,
        max: 120,
    },

    // Booking types
    bookingTypes: [
        {
            id: "instant-booking",
            name: "Instant Booking",
            icon: "⚡",
            description:
                "Guests can book immediately without waiting for your approval. Get more bookings faster!",
        },
        {
            id: "request",
            name: "Request to Book",
            icon: "💬",
            description:
                "Guests send a booking request that you can approve or decline. You have full control over who stays.",
        },
    ],

    // Discount recommendations
    discountRecommendations: {
        weekly: {
            min: 10,
            max: 15,
            default: 10,
            recommended: 12,
        },
        monthly: {
            min: 20,
            max: 30,
            default: 20,
            recommended: 25,
        },
    },

    // Weekend pricing recommendations
    weekendPricing: {
        minIncrease: 15, // 15% minimum increase
        maxIncrease: 40, // 40% maximum increase
        recommendedIncrease: 25, // 25% recommended
    },

    // Validation rules
    minBasePrice: 10,
    maxBasePrice: 10000,
    minStayNights: 1,
    maxStayNights: 365,
    minDiscountPercentage: 0,
    maxDiscountPercentage: 99,

    // Default values for new listings
    defaults: {
        basePrice: 5000,
        // weekendPrice: 0,
        // hasWeekendPrice: false,
        // weeklyDiscount: 0,
        // monthlyDiscount: 0,
        minStay: 1,
        // hasDaySpecificMinStay: false,
        // daySpecificMinStay: {},
        bookingType: "instant-booking",
    },

    // Pricing strategies
    strategies: {
        quickBookings: {
            name: "Quick Bookings",
            description: "Maximize occupancy with competitive pricing",
            priceMultiplier: 0.9, // 10% below average
            recommendedMinStay: 1,
            recommendedWeeklyDiscount: 15,
            recommendedMonthlyDiscount: 25,
        },
        balanced: {
            name: "Balanced",
            description: "Balance between occupancy and profit",
            priceMultiplier: 1.0, // At market average
            recommendedMinStay: 2,
            recommendedWeeklyDiscount: 10,
            recommendedMonthlyDiscount: 20,
        },
        premium: {
            name: "Premium",
            description: "Target quality guests with higher margins",
            priceMultiplier: 1.15, // 15% above average
            recommendedMinStay: 3,
            recommendedWeeklyDiscount: 5,
            recommendedMonthlyDiscount: 15,
        },
    },

    // Market position thresholds
    marketPosition: {
        budget: 0.85, // Below 85% of average = budget
        premium: 1.15, // Above 115% of average = premium
        // Between 85% and 115% = competitive
    },

    // Tips and recommendations
    tips: {
        newListing: [
            "Start 10-15% higher than your target price",
            "Monitor booking rate for first 2-3 weeks",
            "Adjust price if no bookings after 3 weeks",
        ],
        lowBookings: [
            "Consider lowering price by 5-10%",
            "Enable instant booking",
            "Improve photos and description",
            "Add long-stay discounts",
        ],
        highDemand: [
            "Consider increasing weekend prices",
            "Reduce or remove discounts temporarily",
            "Set minimum stay requirements",
        ],
    },
};
