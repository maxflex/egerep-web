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
        $value = Parser::compileVars($value);
        return   Parser::compilePage($this, $value);
    }

    public function scopeFindByParams($query, $search)
    {
        @extract($search);
        $subjects = array_filter($subjects);
        foreach($subjects as $subject_id => $enabled) {
            $query->whereRaw("FIND_IN_SET($subject_id, subjects)");
        }
        $query->where('place', $place)->where('station_id', $station_id)->where('id', '!=', $id);
    }

    public static function boot()
    {
        static::addGlobalScope(new PageScope);
    }
}
