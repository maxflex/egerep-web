<?php

namespace App\Models;

use Shared\Model;
use App\Models\Variable;
use App\Models\Service\Parser;
use App\Scopes\PageScope;
use App\Models\Service\Factory;

class Page extends Model
{

    // Соответствия между разделами и ID предмета
    static $subject_page_id = [
        '1'   => 194,
        '2'   => 195,
        '3'   => 198,
        '4'   => 199,
        '5'   => 203,
        '6'   => 196,
        '7'   => 197,
        '8'   => 201,
        '9'   => 200,
        '10'  => 202,
        '1,2' => 247,
    ];

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

    public function useful()
    {
        return $this->hasMany(PageUseful::class);
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
        if ($this->hidden_filter) {
            $data['hidden_filter'] = explode(',', str_replace(' ', '', mb_strtolower($this->hidden_filter)));
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
        foreach($subjects as $subject_id) {
            $query->whereSubject($subject_id);
        }
        if (isset($place)) {
            $query->where('place', $place);
        }
        if (isset($sort)) {
            $query->where('sort', $sort);
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

    public static function getUrl($id)
    {
        return self::whereId($id)->value('url');
    }

    public static function getSubjectUrl($subject_eng)
    {
        return self::getUrl(Page::$subject_page_id[Factory::getSubjectId($subject_eng)]);
    }
}
