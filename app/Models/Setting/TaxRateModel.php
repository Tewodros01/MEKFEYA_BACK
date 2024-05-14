<?php

namespace app\Models\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TaxRateModel extends Model
{
    use HasFactory;

    protected $table = 'tax_rates';
    protected $fillable = [
        'id',
        'tax_type',
        'tax_rate',
        'created_at',
        'updated_at',
    ];
}
