<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User model schema",
 *     type="object",
 *     required={"id", "name", "email"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2024-12-01T12:00:00Z"),
 *     @OA\Property(property="email_verification_token", type="string", format="date-time", nullable=true, example="2024-12-01T12:00:00Z"),
 *     @OA\Property(property="email_verification_token_expires_at", type="string", format="date-time", nullable=true, example="2024-12-01T12:00:00Z"),
 *     @OA\Property(property="country", type="string", example="RS", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "suspended", "deactivated"}, example="active"),
 *     @OA\Property(property="last_login_at", type="string", format="date-time", nullable=true, example="2024-12-01T12:00:00Z"),
 *     @OA\Property(property="last_login_ip", type="string", format="ipv4", nullable=true, example="192.168.1.1"),
 *     @OA\Property(property="last_login_user_agent", type="string", nullable=true, example="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36"),
 *     @OA\Property(property="terms_accepted_at", type="string", format="date-time", nullable=true, example="2024-12-01T12:00:00Z"),
 *     @OA\Property(property="privacy_policy_accepted_at", type="string", format="date-time", nullable=true, example="2024-12-01T12:00:00Z"),
 *     @OA\Property(property="newsletter_subscription", type="boolean", example=true),
 *     @OA\Property(property="password_changed_at", type="string", format="date-time", nullable=true, example="2024-12-01T12:00:00Z"),
 *     @OA\Property(property="deactivated_at", type="string", format="date-time", nullable=true, example="2024-12-01T12:00:00Z"),
 *     @OA\Property(property="remember_token", type="string", nullable=true, example="abc123xyz"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-02T15:30:00Z")
 * )
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'is_admin',
        'email_verification_token',
        'email_verification_token_expires_at',
        'terms_accepted_at',
        'privacy_policy_accepted_at',
        'last_login_at',
        'last_login_ip',
        'last_login_user_agent',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'is_admin',
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function passwordHistories()
    {
        return $this->hasMany(PasswordHistory::class);
    }
    
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }
}
