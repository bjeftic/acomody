<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Listing extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'description',
        'type_id',
        'user_id',
        'location_id',
        'status',
        'is_active',
        'created_at',
        'updated_at',
    ];

    public function listable()
    {
        return $this->morphTo();
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // public function reviews()
    // {
    //     return $this->morphMany(Review::class, 'reviewable');
    // }
}
