<?php

namespace App\Models\Organaization;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganaizationModel extends Model
{
    use HasFactory;
    protected $table = 'organization';
    protected $fillable = [
        'id',
        'organization_name',
        'country',
        'sub_city',
        'woreda',
        'house_no',
        'address_line',
        'contact_person',
        'contact_email',
        'contact_phone',
        'fax',
        'po_box',
        'website_address',
        'vat_no',
        'vat_reg_date',
        'tin_no',
        'created_at',
        'updated_at',
    ];
}
