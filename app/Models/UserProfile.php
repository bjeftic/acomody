<?php

namespace App\Models;

class UserProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'user_id',
        'bio',
        'website',
        'social_links',
        'preferences',
        'avatar'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
