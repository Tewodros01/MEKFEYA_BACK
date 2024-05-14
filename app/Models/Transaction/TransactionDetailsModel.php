<?php

namespace app\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Transaction\TransactionModel;
use App\Models\Product\ProductModel;

class TransactionDetailModel extends Model
{

    use HasFactory;

    protected $table = 'transaction_details';
    protected $fillable = [
        'id',
        'transaction_id',
        'product_id',
        'quantity_sold',
        'item_price',
        'subtotal',
        'discount',
        'is_refunded',
        'created_at',
        'updated_at',
    ];

    public function transaction()
    {
        return $this->belongsTo(TransactionModel::class, 'transaction_id', 'id');
    }
    public function product()
    {
        return $this->hasOne(ProductModel::class, 'id', 'product_id');
    }
}
