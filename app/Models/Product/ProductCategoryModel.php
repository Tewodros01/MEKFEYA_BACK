<?php

namespace app\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategoryModel extends Model
{
    use HasFactory;

    protected  $table = 'product_categorys';
    protected $fillable = [
        'id',
        'category_name',
        'cat_code',
        'created_at',
        'updated_at',
    ];
}
