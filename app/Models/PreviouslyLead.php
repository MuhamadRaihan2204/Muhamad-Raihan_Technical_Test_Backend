<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreviouslyLead extends Model
{
    protected $fillable = [
        'user_id',
        'lead_id',
        'name',
        'email',
        'phone',
        'status'
    ];
}
