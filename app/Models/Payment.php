<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'name',
        'email',
        'sum',
        'order_id',
        'is_receipt_printed'
    ];
}
