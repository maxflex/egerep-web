<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Service\Parser;

class Variable extends Model
{
    // ID переменной SERP
    const SERP_ID = 20;

    protected $attributes = [
        'name' => 'новая переменная'
    ];

    protected $fillable = [
        'name',
        'html',
        'desc'
    ];

    public function getHtmlAttribute($value)
    {
        return Parser::compileVars($value);
    }

    public function scopeFindByName($query, $name)
    {
        return $query->where('name', $name);
    }
}
