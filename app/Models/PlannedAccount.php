<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlannedAccount extends Model
{
    protected $connection = 'egerep';
    protected $appends = ['user'];

    public function getUserAttribute()
    {
        return egecrm('users')->whereId($this->user_id)
                ->select('id', 'first_name', 'last_name', 'photo_extension')->first();
    }
}
