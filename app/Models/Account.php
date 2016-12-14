<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Account extends Model
{
    // id status a взаимозачетов в таблице payments в NEC
    const MUTUAL_DEBT_STATUS = 6;

    protected $connection = 'egerep';

    protected $appends = ['mutual_debt'];

    public function getMutualDebtAttribute()
    {
        return DB::connection('egecrm')
                 ->table('payments')
                 ->select('sum')
                 ->whereRaw("STR_TO_DATE(date, '%d.%c.%Y') = '{$this->date_end}'")
                 ->where('entity_id', $this->tutor_id)
                 ->where('entity_type', Tutor::USER_TYPE)
                 ->where('id_status', static::MUTUAL_DEBT_STATUS)->first();
    }
}
