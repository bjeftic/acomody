<?php

namespace App\Http\Resources;

use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    private function buildUserCurrencyPricing(): ?array
    {
        $bookingCurrency = $this->currency;
        $userCurrency = CurrencyService::getUserCurrency();

        if ($userCurrency->code === $bookingCurrency) {
            return null;
        }

        $convert = fn (?float $amount): ?float => $amount !== null
            ? calculatePriceInSettedCurrency($amount, $bookingCurrency, $userCurrency->code)
            : null;

        $details = $this->price_details ?? [];

        return [
            'currency' => $userCurrency->code,
            'subtotal' => $convert($this->subtotal),
            'total_price' => $convert($this->total_price),
            'bulk_discount_amount' => isset($details['bulk_discount']['amount'])
                ? $convert($details['bulk_discount']['amount'])
                : null,
        ];
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'booking_type' => $this->booking_type,
            'payment_status' => $this->payment_status->value,

            // Stay
            'check_in' => $this->check_in?->toDateString(),
            'check_out' => $this->check_out?->toDateString(),
            'nights' => $this->nights,
            'guests' => $this->guests,

            // Pricing
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'total_price' => $this->total_price,
            'commission_host' => $this->when($request->user()?->id === $this->host_user_id, $this->commission_host),
            'commission_guest' => $this->when($request->user()?->id === $this->host_user_id, $this->commission_guest),
            'is_commission_free' => $this->when($request->user()?->id === $this->host_user_id, $this->is_commission_free),
            'price_details' => $this->price_details,
            'refund_amount' => $this->refund_amount,

            // Accommodation
            'accommodation' => $this->whenLoaded('accommodation', fn () => [
                'id' => $this->accommodation->id,
                'title' => $this->accommodation->title,
                'address' => $this->accommodation->street_address,
                'cancellation_policy' => $this->accommodation->cancellation_policy,
                'payment_policy' => $this->accommodation->payment_policy?->value,
                'payment_policy_label' => $this->accommodation->payment_policy?->label(),
                'primary_photo_url' => $this->accommodation->relationLoaded('primaryPhoto')
                    ? $this->accommodation->primaryPhoto?->medium_url
                    : null,
            ]),

            // Guest (visible to host)
            'guest' => $this->guest_info
                ? [
                    'id' => $this->guest_info->id,
                    'name' => trim(($this->guest_info->first_name ?? '').' '.($this->guest_info->last_name ?? '')) ?: $this->guest_info->email,
                    'email' => $this->guest_info->email,
                ]
                : $this->whenLoaded('guest', fn () => [
                    'id' => $this->guest->id,
                    'name' => trim(($this->guest->userProfile?->first_name ?? '').' '.($this->guest->userProfile?->last_name ?? '')) ?: $this->guest->email,
                    'email' => $this->guest->email,
                ]),

            // Pricing in user's selected currency
            'pricing_in_user_currency' => $this->buildUserCurrencyPricing(),

            // Notes
            'guest_notes' => $this->guest_notes,
            'cancellation_reason' => $this->cancellation_reason,
            'decline_reason' => $this->decline_reason,

            // Timestamps
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'declined_at' => $this->declined_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
