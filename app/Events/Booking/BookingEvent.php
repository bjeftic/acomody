<?php

namespace App\Events\Booking;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class BookingEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Re-fetch the model without authorization so the queue worker
     * (which has no authenticated user) can restore Booking freely.
     */
    protected function restoreModel($value): Model
    {
        $class = $value->class;
        $find = fn () => $class::with($value->relations ?? [])->findOrFail($value->id);

        if (method_exists($class, 'withoutAuthorization')) {
            return $class::withoutAuthorization($find);
        }

        return $find();
    }
}
