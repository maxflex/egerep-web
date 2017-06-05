<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPayment extends Model
{
    protected $connection = 'egerep';
    protected static $dotDates = ['date'];
}
