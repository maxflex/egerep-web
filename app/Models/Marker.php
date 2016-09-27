<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Marker;

class Marker extends Model
{
    protected $connection = 'egerep';

    protected $with = ['metros'];
    public $timestamps = false;

    // ------------------------------------------------------------------------

    public function markerable()
    {
        return $this->morphTo();
    }

    public function metros()
    {
        return $this->hasMany('App\Models\Metro');
    }
}
