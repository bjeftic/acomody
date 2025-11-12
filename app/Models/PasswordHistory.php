<?php

namespace App\Models;

class PasswordHistory extends Model
{
    protected $fillable = [
        'user_id',
        'password_hash',
        'created_at',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
