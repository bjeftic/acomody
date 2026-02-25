<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'status'         => $this->status->value,
            'status_label'   => $this->status->label(),
            'booking_type'   => $this->booking_type,
            'payment_status' => $this->payment_status->value,

            // Stay
            'check_in'   => $this->check_in?->toDateString(),
            'check_out'  => $this->check_out?->toDateString(),
            'nights'     => $this->nights,
            'guests'     => $this->guests,

            // Pricing
            'currency'       => $this->currency,
            'subtotal'       => $this->subtotal,
            'fees_total'     => $this->fees_total,
            'taxes_total'    => $this->taxes_total,
            'total_price'    => $this->total_price,
            'price_breakdown' => $this->price_breakdown,
            'refund_amount'  => $this->refund_amount,

            // Accommodation
            'accommodation' => $this->whenLoaded('accommodation', fn () => [
                'id'    => $this->accommodation->id,
                'title' => $this->accommodation->title,
                'cancellation_policy' => $this->accommodation->cancellation_policy,
            ]),

            // Guest (visible to host)
            'guest' => $this->whenLoaded('guest', fn () => [
                'id'    => $this->guest->id,
                'name'  => $this->guest->name ?? $this->guest->first_name . ' ' . $this->guest->last_name,
                'email' => $this->guest->email,
            ]),

            // Notes
            'guest_notes'         => $this->guest_notes,
            'cancellation_reason' => $this->cancellation_reason,
            'decline_reason'      => $this->decline_reason,

            // Timestamps
            'confirmed_at'  => $this->confirmed_at?->toIso8601String(),
            'cancelled_at'  => $this->cancelled_at?->toIso8601String(),
            'declined_at'   => $this->declined_at?->toIso8601String(),
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
