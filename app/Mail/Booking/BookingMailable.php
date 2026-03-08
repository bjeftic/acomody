<?php

namespace App\Mail\Booking;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

abstract class BookingMailable extends Mailable
{
    use SerializesModels;

    /**
     * Re-fetch the model without authorization so the queue worker (which has
     * no authenticated user) can load Booking and its relations freely.
     */
    protected function restoreModel($value): Model
    {
        $class = $value->class;
        $find = fn () => $class::with($value->relations ?? [])->findOrFail($value->id);

        return $class::withoutEvents($find);
    }
}
