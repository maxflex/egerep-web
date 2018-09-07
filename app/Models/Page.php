<?php

namespace App\Models;

use Shared\Model;
use App\Models\Variable;
use App\Models\Service\Parser;
use App\Scopes\PageScope;
use App\Models\Service\Factory;

class Page extends Model
{
    const MAX_BLOCK_LINKS        = 11;
    const MAX_BLOCK_LINKS_MOBILE = 8;

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
        '11'  => 1333,
        '1,2' => 247,
        '3,4' => 984,
        '6,7' => 603,
        '8,9' => 250,
    ];

    // also serp fields
    protected $casts = [
       'id'         => 'int', // ID нужен, чтобы идентифицировать текущую страницу в search
       'place'      => 'string',
       'station_id' => 'string',
       'subjects'   => 'string',
       'sort'       => 'string',
       'priority'   => 'string',
   ];

    public function getTitleAttribute($value)
    {
        return $value . ' – Московский репетитор';
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
        if (true) {
            switch($this->priority) {
                case 2:
                    $data['place'] = 'tutor';
                    $data['sort'] = 'nearest-metro';
                    break;
                case 3:
                    $data['place'] = 'home';
                    $data['sort'] = 'nearest-metro';
                    break;
                default:
                    $data['place'] = 'tutor';
                    $data['sort'] = 'most-popular';
                    break;
            }
        }
        return json_encode($data, JSON_FORCE_OBJECT);
    }

    public function getHtmlAttribute($value)
    {
        $variable_name = 'serp';
        if (isDevSubdomain()) {
            $variable_name = 'serp-dev';
        }
        if ($_COOKIE[AB_TEST_KEY]) {
            $variable_name = 'serp-dev';
        }
        $value = Variable::display($variable_name);
        Parser::compileSeo($this, $value);
        return Parser::compilePage($this, $value);
    }

    public function getH1Attribute($value)
    {
        if ($value) {
            return "<h1 class='h1-top'>{$value}</h1>";
        }
        return ' ';
    }

    public function getH1BottomAttribute($value)
    {
        // отображать нижний h1 только в случае,
        // если поле html заполнено
        if ($value && ! empty(trim($this->getClean('html')))) {
            return "<h2 class='h1-bottom'>{$value}</h2>";
        }
        return ' ';
    }

    public function scopeWhereSubject($query, $subject_id)
    {
        return $query->whereRaw("FIND_IN_SET($subject_id, subjects)");;
    }

    public function scopeFindByParams($query, $search)
    {
        @extract($search);

        $query->where('subjects', implode(',', $subjects));
        $query->where('place', setOrNull(@$place));
        $query->where('sort', setOrNull(@$sort));
        // учитываем метро только в том случае, если указана соответствующая сортировка
        if (isset($sort) && $sort == 5) {
            $query->where('station_id', setOrNull(@$station_id));
        }
        $query->where('id', '!=', $id);

        return $query;
    }

    public static function boot()
    {
        static::addGlobalScope(new PageScope);
    }

    public static function getUrl($id)
    {
        return '/' . self::whereId($id)->value('url');
    }

    public static function getSubjectUrl($subject_eng)
    {
        return self::getUrl(Page::$subject_page_id[Factory::getSubjectId($subject_eng)]);
    }

    public static function getSubjectRoutes()
    {
        $subject_routes = [];
        foreach(self::$subject_page_id as $subject_id => $page_id) {
            // ссылки только к отдельным предметам
            if (strpos($subject_id, ',') === false) {
                $subject_routes[$subject_id] = self::getUrl($page_id);
            }
        }
        return $subject_routes;
    }

    /**
     * Главная страница серпа
     */
    public function isMainSerp()
    {
        return $this->id == 10;
    }

    public static function getBlockLinks($block_id)
    {
        return self::publishedAnchors($block_id)->get()->all();
    }

    public static function getPageLinks($page_id)
    {
        return self::publishedAnchors(4)->where('anchor_page_id', $page_id)->get()->all();
    }

    public function scopePublishedAnchors($query, $block_id) {
        return $query->where('anchor_block_id', $block_id)
                     ->where('published', 1)
                     ->select('anchor', 'url', 'h1')
                     ->orderBy('anchor')->orderBy('h1');
    }
}
