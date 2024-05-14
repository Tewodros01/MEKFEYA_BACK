<?php

namespace app\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Product\ProductCategoryModel;
use App\Models\Setting\TaxRateModel;
use App\Models\Setting\LocationModel;
use App\Models\Setting\UnitOfMeasurementModel;
use App\Models\User;



class ProductModel extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $fillable = [
        'id',
        'product_description',
        'product_code',
        'prc_code',
        'product_category_id',
        'unit_cost',
        'unit_of_measurement',
        'unit_price',
        'product_quantity',
        'product_image',
        'productimage_path',
        'tax_rate_id',
        'remark',
        'is_active',
        'location_id',
        'reg_by_id',
        'purchase_date',
        'exp_date',
        'created_at',
        'updated_at',

    ];
    public function category()
    {
        return $this->hasMany(ProductCategoryModel::class, 'id', 'product_category_id');
    }
    public function taxRate()
    {
        return $this->hasOne(TaxRateModel::class, 'id', 'tax_rate_id');
    }
    public function location()
    {
        return $this->hasMany(LocationModel::class, 'id', 'location_id');
    }
    public function unit(){
        return $this->hasOne(UnitOfMeasurementModel::class, 'id', 'unit_of_measurement');
    }
    public function regBy()
    {
        return $this->hasMany(User::class, 'id', 'reg_by_id');
    }
}
