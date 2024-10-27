<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'account_name',
        'account_password',
        'amount',
        'bank_type',
        'email',
        'image',
        'user_id'
    ];
}
