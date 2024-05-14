<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction\TransactionModel;
use App\Models\Transaction\TransactionDetailModel;
use App\Models\Product\ProductModel;
use App\Models\User;
use App\Models\Setting\LocationModel;

class RefundModel extends Model
{
    use HasFactory;

    protected $table = 'refunds';
    protected $fillable = [
        'rf_no',
        'transaction_id',
        'transactiondetail_id',
        'refund_by',
        'location_id',
        'refund_date',
        'refunded_amount',
        'refunded_in',
        'invoice_no',
        'refund_reson',
        'created_at',
        'updated_at',
    ];

    public function transaction() {
        return $this->hasOne(TransactionModel::class, 'id','transaction_id'); 
    }
    public function transactionDetails() {
        return $this->hasMany(TransactionDetailModel::class, 'id','transactiondetail_id');
    }
    public function refundBy() {
        return $this->hasMany(User::class, 'id','refund_by');
    }
    public function location() {
        return $this->hasOne(LocationModel::class, 'id','location_id');
    }

}
