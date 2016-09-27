<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $connection = 'egerep';
    
    protected $appends = ['color'];

    const LINE_COLORS = [
        1 => '#EF1E25',	// Красный
        2 => '#029A55', // Зеленый
        3 => '#0252A2', // Синий
        4 => '#019EE0', // Голубой
        5 => '#745C2F', // Коричневый
        6 => '#C07911', // Оранжевый
        7 => '#B61D8E', // Фиолетовый
        8 => '#FFD803',	// Желтый
        9 => '#ACADAF', // Серый
        10 => '#B1D332',// Салатовый
        11 => '#5091BB',// Бледно-синий (Варшавская)
        12 => '#85D4F3',// Светло-голубая (Бульвар Адмирала Ушакова)
    ];
    // ------------------------------------------------------------------------

    public function getColorAttribute()
    {
        return static::LINE_COLORS[$this->line_id];
    }
}
