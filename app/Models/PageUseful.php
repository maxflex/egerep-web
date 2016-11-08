<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageUseful extends Model
{
    protected $table = 'page_useful';
    public $timestamps = false;
    protected $fillable = ['text', 'page_id_field'];

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id_field');
    }

    public function getTextAttribute($value)
    {
        if (empty($value)) {
            return $this->page->keyphrase;
        }
        return $value;
    }
}
