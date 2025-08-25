<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="UserProfile",
 *     title="User Profile",
 *     description="User profile model schema",
 *     type="object",
 *     required={"id", "user_id", "name"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="bio", type="string", example="Software developer with a passion for open source."),
 *     @OA\Property(property="website", type="string", format="uri", example="https://johndoe.com"),
 *     @OA\Property(property="social_links", type="object", example={"twitter": "https://twitter.com/johndoe", "github": "https://github.com/johndoe"}),
 *     @OA\Property(property="preferences", type="object", example={"theme": "dark", "notifications": true}),
 *     @OA\Property(property="avatar", type="string", format="uri", example="https://example.com/avatars/johndoe.png"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-02T15:30:00Z")
 * )
 */
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

}
