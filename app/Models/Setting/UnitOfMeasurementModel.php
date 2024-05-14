<?php

namespace app\Models\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitOfMeasurementModel extends Model
{
    use HasFactory;

    protected $table = 'unit_of_measurement';

    protected $fillable = ['measure', 'unit'];
}
