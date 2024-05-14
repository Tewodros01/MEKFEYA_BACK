<?php

namespace app\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Transactions\TransactionsModel;
use Psy\Readline\Transient;

class PaymentsModel extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $fillable = [
        'id',
        'payment_mode',
        'transaction_id',
        'payment_transaction_no',
        'paid_amount',
        'created_at',
        'updated_at',
    ];
    public function transaction(){
        return $this->belongsTo(TransactionsModel::class, 'id','transaction_id');
    }
}
