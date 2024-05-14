<?php

namespace app\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Customer\CustomerModel;
use App\Models\Transaction\PaymentsModel;
use App\Models\Setting\LocationModel;
use App\Models\Transaction\TransactionDetailModel;

class TransactionModel extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    protected $fillable = [
        'id',
        'user_id',
        'customer_id',
        'total_amount',
        'transaction_date',
        'invoice_no',
        'sales_type',
        'fs_no',
        'total_vat',
        'total_discount',
        'change_amount',
        'is_void',
        'void_remark',
        'void_by',
        'void_date',
        'location_id',
        'is_refunded',
        'created_at',
        'updated_at',

    ];
    public function transactionDetails(){
        return $this->hasMany(TransactionDetailModel::class,'transaction_id','id');
    }

    public function salesBy()
    {
        return $this->hasMany(User::class, 'id', 'user_id');
    }
    public function customer()
    {
        return $this->hasMany(CustomerModel::class, 'id', 'customer_id');
    }
    public function payment()
    {
        return $this->hasMany(PaymentsModel::class, 'transaction_id', 'id');
    }
    public function location()
    {
        return $this->hasMany(LocationModel::class, 'id', 'location_id');
    }
}
