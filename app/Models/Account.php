<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Account extends Model
{
    // id status a взаимозачетов в таблице payments в NEC
    const MUTUAL_DEBT_STATUS = 6;

    protected $connection = 'egerep';

    protected $appends = ['full_sum'];

    public function payments()
    {
        return $this->hasMany(AccountPayment::class);
    }

    /**
     * Сумма включая взаимозачет
     */
    public function getFullSumAttribute()
    {
        $payments_sum = $this->payments()->sum('sum');
        $mutual_payments_sum = egecrm('payments')->where('id_status', 6)->where('account_id', $this->id)->sum('sum');
        return intval($payments_sum) + intval($mutual_payments_sum);
    }
}
