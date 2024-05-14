<?php

namespace app\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CustomerModel extends Model
{
    use HasFactory;

    protected $table = 'customers';
    protected $fillable = [
        'id',
        'full_name',
        'contact_person',
        'tin_number',
        'phone_number',
        'email',
        'vat_reg_number',
        'created_at',
        'updated_at',
    ];
}
