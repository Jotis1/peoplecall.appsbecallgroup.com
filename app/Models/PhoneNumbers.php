<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneNumbers extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'original_operator',
        'current_operator',
        'prefix',
        'type',
        'type_description',
        'last_portability',
    ];

}
