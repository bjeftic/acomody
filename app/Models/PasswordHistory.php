<?php

namespace App\Models;

class PasswordHistory extends Model
{
    protected $fillable = [
        'user_id',
        'password_hash',
        'created_at',
    ];

    public function canBeReadBy($user): bool
    {
        return true;
    }

    public function canBeCreatedBy($user): bool
    {
        return $user !== null;
    }

    public function canBeUpdatedBy($user): bool
    {
        return true;
    }

    public function canBeDeletedBy($user): bool
    {
        return true;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
