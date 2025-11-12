// House Rules Configuration
export const houseRulesConfig = {
    // Time slots for check-in/check-out/quiet hours (24-hour format)
    timeSlots: [
        "00:00", "01:00", "02:00", "03:00", "04:00", "05:00",
        "06:00", "07:00", "08:00", "09:00", "10:00", "11:00",
        "12:00", "13:00", "14:00", "15:00", "16:00", "17:00",
        "18:00", "19:00", "20:00", "21:00", "22:00", "23:00",
    ],

    // Standard rules configuration
    standardRules: [
        {
            id: "allowSmoking",
            name: "Smoking",
            description: "Allow smoking inside the property",
            icon: "🚬",
            defaultValue: false,
            tip: "Most guests prefer non-smoking properties. Consider designating outdoor smoking areas.",
        },
        {
            id: "allowPets",
            name: "Pets",
            description: "Allow guests to bring pets",
            icon: "🐾",
            defaultValue: false,
            tip: "Pet-friendly properties attract 30% more bookings but may require additional cleaning.",
        },
        {
            id: "allowEvents",
            name: "Events and parties",
            description: "Allow guests to host events or parties",
            icon: "🎉",
            defaultValue: false,
            tip: "Consider your neighbors and local noise ordinances before allowing events.",
        },
        {
            id: "allowChildren",
            name: "Children",
            description: "Suitable for children (2-12 years)",
            icon: "👶",
            defaultValue: true,
            tip: "Child-friendly properties appeal to family travelers. Ensure safety features are in place.",
        },
        {
            id: "allowInfants",
            name: "Infants",
            description: "Suitable for infants (under 2 years)",
            icon: "🍼",
            defaultValue: true,
            tip: "Consider providing a crib or high chair to attract families with infants.",
        },
    ],

    // Cancellation policies
    cancellationPolicies: [
        {
            id: "flexible",
            name: "Flexible",
            description: "Full refund 1 day prior to arrival, except fees.",
            icon: "✅",
            bookingImpact: "High - Attracts more bookings",
            recommendedFor: "New hosts, competitive markets, low season",
            details: [
                "Guests can cancel up to 24 hours before check-in for a full refund",
                "If they cancel less than 24 hours before check-in, the first night is non-refundable",
                "Service fees are refunded when cancellation happens before check-in",
            ],
        },
        {
            id: "moderate",
            name: "Moderate",
            description: "Full refund 5 days prior to arrival, except fees.",
            icon: "⚖️",
            bookingImpact: "Medium - Balanced approach",
            recommendedFor: "Most hosts, year-round bookings",
            details: [
                "Guests can cancel up to 5 days before check-in for a full refund",
                "If they cancel less than 5 days before check-in, the first night is non-refundable",
                "50% refund for cancellations made 5-30 days before check-in",
                "Service fees are refunded when cancellation happens before check-in",
            ],
        },
        {
            id: "firm",
            name: "Firm",
            description: "50% refund up until 30 days prior to arrival, except fees. No refund after that.",
            icon: "🔒",
            bookingImpact: "Low-Medium - More protection for hosts",
            recommendedFor: "High-demand properties, peak season",
            details: [
                "Guests can cancel up to 30 days before check-in for a 50% refund",
                "No refund for cancellations made less than 30 days before check-in",
                "Service fees are non-refundable",
            ],
        },
        {
            id: "strict",
            name: "Strict",
            description: "50% refund up until 7 days prior to arrival, except fees. No refund after that.",
            icon: "⛔",
            bookingImpact: "Low - Fewer bookings, maximum protection",
            recommendedFor: "Luxury properties, special events, high-value bookings",
            details: [
                "Guests can cancel up to 7 days before check-in for a 50% refund",
                "No refund for cancellations made less than 7 days before check-in",
                "Service fees are non-refundable",
            ],
        },
        {
            id: "non-refundable",
            name: "Non-Refundable",
            description: "No refunds for any cancellations.",
            icon: "🚫",
            bookingImpact: "Very Low - Use with caution",
            recommendedFor: "Special promotions, last-minute discounted bookings",
            details: [
                "No refunds for cancellations at any time",
                "Guests pay 10-15% less for this option",
                "Service fees are non-refundable",
                "Best used as an optional upgrade for guests who are certain of their plans",
            ],
        },
    ],

    // Check-in methods
    checkInMethods: [
        {
            id: "self-checkin",
            name: "Self Check-in",
            icon: "🔑",
            description: "Guests can check in without meeting you",
            options: ["Keypad", "Lockbox", "Smart Lock", "Building Staff"],
        },
        {
            id: "meet-in-person",
            name: "Meet in Person",
            icon: "🤝",
            description: "You'll meet guests to hand over keys",
            options: ["At property", "Nearby location"],
        },
    ],

    // Common additional rules templates
    additionalRulesTemplates: [
        "Please remove shoes at the entrance",
        "No loud music after 10 PM",
        "Keep the garden gate closed at all times",
        "Please turn off all lights and AC when leaving",
        "No cooking with strong-smelling foods (e.g., fish)",
        "Please respect our neighbors and keep noise to a minimum",
        "Guests are responsible for any damages",
        "Maximum occupancy is strictly enforced",
        "Only registered guests are allowed on the property",
        "Please take out trash before checkout",
    ],

    // Default values
    defaults: {
        // Check-in/out times
        checkInFrom: "15:00",
        checkInUntil: "20:00",
        checkOutUntil: "11:00",

        // Quiet hours
        hasQuietHours: false,
        quietHoursFrom: "22:00",
        quietHoursUntil: "08:00",

        // Standard rules
        allowSmoking: false,
        allowPets: false,
        allowEvents: false,
        allowChildren: true,
        allowInfants: true,

        // Policies
        cancellationPolicy: "moderate",

        // Additional
        additionalRules: "",
        checkInMethod: "self-checkin",
    },

    // Recommended settings by property type
    recommendedByType: {
        apartment: {
            allowSmoking: false,
            allowPets: false,
            allowEvents: false,
            hasQuietHours: true,
            quietHoursFrom: "22:00",
            quietHoursUntil: "08:00",
            cancellationPolicy: "moderate",
        },
        house: {
            allowSmoking: false,
            allowPets: true,
            allowEvents: false,
            hasQuietHours: false,
            cancellationPolicy: "moderate",
        },
        villa: {
            allowSmoking: false,
            allowPets: true,
            allowEvents: true,
            hasQuietHours: false,
            cancellationPolicy: "firm",
        },
        cabin: {
            allowSmoking: false,
            allowPets: true,
            allowEvents: false,
            hasQuietHours: false,
            cancellationPolicy: "moderate",
        },
    },

    // Tips for hosts
    tips: {
        checkIn: [
            "Flexible check-in times (2-3 hour window) attract more bookings",
            "Late check-in options (after 8 PM) increase convenience",
            "Self check-in is preferred by 70% of guests",
        ],
        checkOut: [
            "Standard checkout is 10-11 AM",
            "Offer late checkout for an additional fee",
            "Clear checkout instructions reduce issues",
        ],
        quietHours: [
            "Quiet hours are especially important in apartments",
            "Consider local noise ordinances",
            "22:00-08:00 is the most common quiet period",
        ],
        cancellation: [
            "Flexible policies get 40% more bookings",
            "Moderate policy is best for most properties",
            "Stricter policies work better in high-demand periods",
        ],
    },

    // Validation rules
    validation: {
        checkInFromBeforeUntil: true, // checkInFrom must be before checkInUntil
        minCheckInWindow: 1, // Minimum 1 hour check-in window
        maxCheckInWindow: 12, // Maximum 12 hour check-in window
        checkOutAfterCheckIn: true, // checkOut must be after checkInFrom (next day)
        additionalRulesMaxLength: 500, // Maximum characters for additional rules
    },
};
