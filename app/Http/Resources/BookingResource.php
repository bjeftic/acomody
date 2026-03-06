<?php

namespace App\Http\Resources;

use App\Enums\Booking\BookingStatus;
use App\Enums\Booking\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => (int) $this->id,
            'status'         => $this->status,
            'status_label'   => ($this->status instanceof BookingStatus ? $this->status : BookingStatus::from($this->status))->label(),
            'booking_type'   => $this->booking_type,
            'payment_status' => $this->payment_status,
            'payment_status_label' => ($this->payment_status instanceof PaymentStatus ? $this->payment_status : PaymentStatus::from($this->payment_status))->label(),

            // Stay
            'check_in'   => $this->check_in ? Carbon::parse($this->check_in)->toDateString() : null,
            'check_out'  => $this->check_out ? Carbon::parse($this->check_out)->toDateString() : null,
            'nights'     => (int) $this->nights,
            'guests'     => (int) $this->guests,


            // Pricing
            'currency'       => $this->currency,
            'subtotal'       => (float) $this->subtotal,
            'fees_total'     => (float) $this->fees_total,
            'taxes_total'    => (float) $this->taxes_total,
            'total_price'    => (float) $this->total_price,
            'price_details'  => is_string($this->price_details) ? json_decode($this->price_details, true) : $this->price_details,
            'refund_amount'  => (float) $this->refund_amount,

            // Accommodation
            'accommodation' => [
                'id'    => (int) $this->accommodation_id,
                'title' => $this->accommodation_title,
                'address' => $this->accommodation_address,
                'cancellation_policy' => $this->accommodation_cancellation_policy,
                'primary_photo_url' => $this->primary_photo_medium_path ?? null,
            ],

            // Guest (visible to host)
            'guest' => [
                'id'    => (int) $this->guest_id,
                'name'  => trim($this->guest_first_name . ' ' . $this->guest_last_name),
                'email' => $this->guest_email,
            ],

            // Notes
            'guest_notes'         => $this->guest_notes,
            'cancellation_reason' => $this->cancellation_reason,
            'decline_reason'      => $this->decline_reason,

            // Timestamps
            'confirmed_at' => $this->confirmed_at ? Carbon::parse($this->confirmed_at)->toIso8601String() : null,
            'cancelled_at' => $this->cancelled_at ? Carbon::parse($this->cancelled_at)->toIso8601String() : null,
            'declined_at'  => $this->declined_at ? Carbon::parse($this->declined_at)->toIso8601String() : null,
            'created_at'   => $this->created_at ? Carbon::parse($this->created_at)->toIso8601String() : null,
        ];
    }
}
