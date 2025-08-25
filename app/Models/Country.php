<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Country",
 *     title="Country",
 *     description="Country model schema",
 *     type="object",
 *     required={"id", "name", "iso_code"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="United States"),
 *     @OA\Property(property="iso_code", type="string", example="US"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-02T15:30:00Z")
 * )
 */
class Country extends Model
{
    //
}
