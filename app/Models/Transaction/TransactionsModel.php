<?php

namespace app\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User\User;
use App\Models\Customer\CustomerModel;
use App\Models\Transactions\PaymentsModel;
use App\Models\Setting\LocationModel;

class TransactionsModel extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    protected $fillable = [
        'id',
        'user_id',
        'customer_id',
        'payment_id',
        'total_amount',
        'transaction_date',
        'invoice_no',
        'sales_type',
        'fs_no',
        'total_discount',
        'is_void',
        'void_remark',
        'location_id',
        'is_refunded',
        'created_at',
        'updated_at',

    ];

    public function salesBy(){
        return $this->hasMany(User::class ,'id', 'user_id');
    }
    public function customer(){
        return $this->hasMany(CustomerModel::class ,'id', 'customer_id');
    }
    public function payment(){
        return $this->hasMany(PaymentsModel::class ,'id', 'payment_id');
    }
    public function location(){
        return $this->hasMany(LocationModel::class ,'id', 'location_id');
    }
}
