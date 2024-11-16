<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantInfo extends Model
{
    protected $table = 'merchant_infos';
    protected $primaryKey = 'merchant_id';

    public $fillable = [
        'merchant_name',
        'merchant_phone',
        'merchant_phone2',
        'merchant_email',
        'merchant_aadhar_no',
        'merchant_pan_no',
        'merchant_profile',
        'merchant_city',
        'merchant_state',
        'merchant_country',
        'merchant_zip',
        'merchant_landmark',
        'merchant_password',
        'merchant_plain_password',
        'merchant_is_onboarded',
        'merchant_is_verified',
        'merchant_status',
    ];
}
