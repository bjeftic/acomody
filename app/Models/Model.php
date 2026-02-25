<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use App\Traits\Authorizable;

abstract class Model extends BaseModel
{
    use Authorizable;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [];
}
