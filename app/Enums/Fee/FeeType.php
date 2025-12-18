<?php

namespace App\Enums\Fee;

enum FeeType: string
{
    // Accommodation fees
    case CLEANING = 'cleaning';
    case GUEST_SERVICE = 'guest_service';
    case EXTRA_GUEST = 'extra_guest';

    // Restaurant fees
    case SERVICE_CHARGE = 'service_charge';
    case CORKAGE = 'corkage';
    case RESERVATION = 'reservation';
    case CANCELLATION = 'cancellation';
    case NO_SHOW = 'no_show';

    // Service fees
    case BOOKING = 'booking';
    case PROCESSING = 'processing';
    case EQUIPMENT = 'equipment';
    case INSURANCE = 'insurance';
    case TRAVEL = 'travel';

    // Event fees
    case TICKET_PROCESSING = 'ticket_processing';
    case REFUND_PROCESSING = 'refund_processing';

    // Universal fees
    case DELIVERY = 'delivery';
    case SETUP = 'setup';
    case TAKEDOWN = 'takedown';
    case LATE_FEE = 'late_fee';
    case EARLY_FEE = 'early_fee';
    case DAMAGE_DEPOSIT = 'damage_deposit';
    case SECURITY_DEPOSIT = 'security_deposit';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match($this) {
            self::CLEANING => __('enums/feeType.cleaning'),
            self::GUEST_SERVICE => __('enums/feeType.guest_service'),
            self::EXTRA_GUEST => __('enums/feeType.extra_guest'),
            self::SERVICE_CHARGE => __('enums/feeType.service_charge'),
            self::CORKAGE => __('enums/feeType.corkage'),
            self::RESERVATION => __('enums/feeType.reservation'),
            self::CANCELLATION => __('enums/feeType.cancellation'),
            self::NO_SHOW => __('enums/feeType.no_show'),
            self::BOOKING => __('enums/feeType.booking'),
            self::PROCESSING => __('enums/feeType.processing'),
            self::EQUIPMENT => __('enums/feeType.equipment'),
            self::INSURANCE => __('enums/feeType.insurance'),
            self::TRAVEL => __('enums/feeType.travel'),
            self::TICKET_PROCESSING => __('enums/feeType.ticket_processing'),
            self::REFUND_PROCESSING => __('enums/feeType.refund_processing'),
            self::DELIVERY => __('enums/feeType.delivery'),
            self::SETUP => __('enums/feeType.setup'),
            self::TAKEDOWN => __('enums/feeType.takedown'),
            self::LATE_FEE => __('enums/feeType.late_fee'),
            self::EARLY_FEE => __('enums/feeType.early_fee'),
            self::DAMAGE_DEPOSIT => __('enums/feeType.damage_deposit'),
            self::SECURITY_DEPOSIT => __('enums/feeType.security_deposit'),
            self::CUSTOM => __('enums/feeType.custom'),
        };
    }

    public function listing(): string
    {
        return match($this) {
            self::CLEANING => 'accommodation',
            self::GUEST_SERVICE => 'accommodation',
            self::EXTRA_GUEST => 'accommodation',
            self::SERVICE_CHARGE => 'restaurant',
            self::CORKAGE => 'restaurant',
            self::RESERVATION => 'restaurant',
            self::CANCELLATION => 'restaurant',
            self::NO_SHOW => 'restaurant',
            self::BOOKING => 'service',
            self::PROCESSING => 'service',
            self::EQUIPMENT => 'service',
            self::INSURANCE => 'service',
            self::TRAVEL => 'service',
            self::TICKET_PROCESSING => 'event',
            self::REFUND_PROCESSING => 'event',
            self::DELIVERY => 'universal',
            self::SETUP => 'universal',
            self::TAKEDOWN => 'universal',
            self::LATE_FEE => 'universal',
            self::EARLY_FEE => 'universal',
            self::DAMAGE_DEPOSIT => 'universal',
            self::SECURITY_DEPOSIT => 'universal',
            self::CUSTOM => 'universal',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::CLEANING => __('enums/feeType.descriptions.cleaning'),
            self::GUEST_SERVICE => __('enums/feeType.descriptions.guest_service'),
            self::EXTRA_GUEST => __('enums/feeType.descriptions.extra_guest'),
            self::SERVICE_CHARGE => __('enums/feeType.descriptions.service_charge'),
            self::CORKAGE => __('enums/feeType.descriptions.corkage'),
            self::RESERVATION => __('enums/feeType.descriptions.reservation'),
            self::CANCELLATION => __('enums/feeType.descriptions.cancellation'),
            self::NO_SHOW => __('enums/feeType.descriptions.no_show'),
            self::BOOKING => __('enums/feeType.descriptions.booking'),
            self::PROCESSING => __('enums/feeType.descriptions.processing'),
            self::EQUIPMENT => __('enums/feeType.descriptions.equipment'),
            self::INSURANCE => __('enums/feeType.descriptions.insurance'),
            self::TRAVEL => __('enums/feeType.descriptions.travel'),
            self::TICKET_PROCESSING => __('enums/feeType.descriptions.ticket_processing'),
            self::REFUND_PROCESSING => __('enums/feeType.descriptions.refund_processing'),
            self::DELIVERY => __('enums/feeType.descriptions.delivery'),
            self::SETUP => __('enums/feeType.descriptions.setup'),
            self::TAKEDOWN => __('enums/feeType.descriptions.takedown'),
            self::LATE_FEE => __('enums/feeType.descriptions.late_fee'),
            self::EARLY_FEE => __('enums/feeType.descriptions.early_fee'),
            self::DAMAGE_DEPOSIT => __('enums/feeType.descriptions.damage_deposit'),
            self::SECURITY_DEPOSIT => __('enums/feeType.descriptions.security_deposit'),
            self::CUSTOM => __('enums/feeType.descriptions.custom'),
        };
    }

    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'name' => $case->label(),
            'description' => $case->description(),
            'listing' => $case->listing(),
        ], self::cases());
    }
}
