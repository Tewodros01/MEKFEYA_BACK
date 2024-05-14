<?php

namespace app\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Product\ProductModel;

class InventoryModel extends Model{

    use HasFactory;

   protected  $tableName = 'inventory';
   protected $fillable = [
        'id' ,
        'product_id' ,
        'quantity',
        'created_at' ,
        'updated_at' ,
    ];

    public function product(){
        return $this->hasMany(ProductModel::class, 'id', 'product_id');
    }
     
}