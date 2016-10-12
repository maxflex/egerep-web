<?php

namespace App\Models;

use Shared\Model;
use App\Models\Variable;
use App\Models\Service\Parser;
use App\Scopes\PageScope;

class Page extends Model
{
    // also serp fields
    protected $casts = [
       'id'         => 'int', // ID нужен, чтобы идентифицировать текущую страницу в search
       'place'      => 'string',
       'station_id' => 'string',
       'subjects'   => 'string',
       'sort'       => 'string',
   ];

   public function variable()
   {
       return $this->belongsTo(Variable::class);
   }

    public function getSubjectsAttribute($value)
    {
        if ($value) {
            $subjects = explode(',', $value);
            foreach($subjects as $subject_id) {
                $return[$subject_id] = true;
            }
            return (object)$return;
        } else {
            return emptyObject();
        }
    }

    public function getSearchAttribute()
    {
        foreach($this->casts as $field => $type) {
            $data[$field] = $this->{$field};
        }
        return json_encode($data, JSON_FORCE_OBJECT);
    }

    public function getHtmlAttribute($value)
    {
        // если расширение от шаблона
        if ($this->variable_id) {
            $value = $this->variable->html;
            Parser::compileSeo($this, $value);
        } else {
            $value = Parser::compileVars($value);
        }
        return Parser::compilePage($this, $value);
    }

    public function scopeWhereSubject($query, $subject_id)
    {
        return $query->whereRaw("FIND_IN_SET($subject_id, subjects)");;
    }

    public function scopeFindByParams($query, $search)
    {
        @extract($search);
        \Log::info('Subjects: ' . json_encode($subjects));
        foreach($subjects as $subject_id) {
            $query->whereSubject($subject_id);
        }
        if (isset($place)) {
            $query->where('place', $place);
        }
        if (isset($station_id)) {
            $query->where('station_id', $station_id);
        }
        if (isset($id)) {
            $query->where('id', '!=', $id);
        }
        return $query;
    }


    public static function boot()
    {
        static::addGlobalScope(new PageScope);
    }
}
