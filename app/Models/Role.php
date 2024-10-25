<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name'
    ];

    // 1 superadmin
    // 2 customer service
    // 3 sales person
    // 4 operational
    // 5 client
}
