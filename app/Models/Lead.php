<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'phone',
        'status',
        'name'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function followUp()
    {
        return $this->hasMany(FollowUp::class, 'lead_id', 'id');
    }
}
