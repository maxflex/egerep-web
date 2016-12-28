<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $connection = 'egerep';
    
    protected $appends = ['color'];

    // ------------------------------------------------------------------------

    public function getColorAttribute()
    {
        return Metro::LINE_COLORS[$this->line_id];
    }
}
