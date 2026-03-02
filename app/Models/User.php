<?php

namespace App\Models;

use App\Mail\Auth\ResetPasswordMail;
use App\Mail\Auth\VerifyEmailMail;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail as AuthMustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\HasApiTokens;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Authenticatable, AuthMustVerifyEmail, Authorizable, CanResetPassword, HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'is_superadmin',
        'email_verification_token',
        'email_verification_token_expires_at',
        'terms_accepted_at',
        'privacy_policy_accepted_at',
        'last_login_at',
        'last_login_ip',
        'last_login_user_agent',
        'registration_ip',
        'preferred_currency',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'is_superadmin',
        'password',
        'remember_token',
    ];

    public function canBeReadBy($user): bool
    {
        if (! $user) {
            return true;
        }

        return $user->id === $this->id;
    }

    public function canBeCreatedBy($user): bool
    {
        return true;
    }

    public function canBeUpdatedBy($user): bool
    {
        return $user && $user->id === $this->id;
    }

    public function canBeDeletedBy($user): bool
    {
        return $user && $user->id === $this->id;
    }

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

    public function sendPasswordResetNotification($token): void
    {
        $frontendUrl = config('app.frontend_url', config('app.url'));
        $resetUrl = $frontendUrl.'/reset-password?token='.$token.'&email='.urlencode($this->email);

        Mail::to($this->email)->queue(new ResetPasswordMail($this, $resetUrl));
    }

    /**
     * Send the email verification notification (used for resend requests).
     */
    public function sendEmailVerificationNotification(): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $this->getKey(),
                'hash' => sha1($this->getEmailForVerification()),
            ]
        );

        Mail::to($this->email)->queue(new VerifyEmailMail($this, $verificationUrl));
    }
}
