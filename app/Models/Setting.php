<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'company_name',
        'address1',
        'address2',
        'city',
        'state',
        'postcode',
        'phone',
        'email',
        'logo_path',
    ];

    public static function instance(): self
    {
        return self::firstOrCreate(['id' => 1]);
    }
}
