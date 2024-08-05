<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpWhitelist extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
    ];

    public static function isWhitelisted($ip)
    {
        // En local no se valida la ip
        if (app()->environment('local')) {
            return true;
        }
        return static::where('ip', $ip)->exists();
    }

    public static function add($ip)
    {
        return static::create(['ip' => $ip]);
    }

    public static function remove($ip)
    {
        return static::where('ip', $ip)->delete();
    }
}
