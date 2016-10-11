<?php

namespace App\Models;

use Shared\Model;
use App\Scopes\TutorScope;
use App\Scopes\ReviewScope;
use DB;

class Tutor extends Model
{
    protected $connection = 'egerep';

    protected $appends = [
        'photo_url',
        'review_avg',
        'reviews_count'
    ];

    const SERVER_URL = 'https://lk.ege-repetitor.ru/img/tutors/';
    const NO_PHOTO   = 'no-profile-img.gif';

    protected $commaSeparated = ['svg_map', 'subjects', 'grades', 'branches'];

    public function markers()
    {
        return $this->morphMany('App\Models\Marker', 'markerable')->where('type', 'green');
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo_extension) {
            return self::SERVER_URL . $this->id . '.' . $this->photo_extension;
        } else {
            return self::SERVER_URL . self::NO_PHOTO;
        }
    }

    public function getReviewsCountAttribute()
    {
        return static::reviews($this->id)->count();
    }

    public function getReviewAvgAttribute()
    {
        $query = Review::withoutGlobalScope(ReviewScope::class)->join('attachments', 'attachments.id', '=', 'attachment_id')->where('tutor_id', $this->id)->whereBetween('score', [1, 10]);
        $sum = $query->newQuery()->sum('reviews.score');
        $count = $query->newQuery()->count();
        switch($this->js) {
            case 10: {
                $js = 8;
                break;
            }
            case 8: {
                $js = 10;
                break;
            }
            default: {
                $js = $this->js;
            }
        }
        if ($this->id == 5) {
            \Log::info($sum . ' | ' . $count);
        }
        $avg = (4 * (($this->lk + $this->tb + $js) / 3) + $sum)/(4 + $count);
        return number_format($avg, 1, ',', '');
    }

    public static function reviews($tutor_id)
    {
        return DB::connection('egerep')
            ->table('reviews')
            ->join('attachments', 'attachments.id', '=', 'attachment_id')
            ->where('tutor_id', $tutor_id)
            ->where('state', 'published')
            ->whereBetween('score', [1, 10]);
    }

    public function scopeWhereSubject($query, $subject_id)
    {
        return $query->whereRaw("FIND_IN_SET($subject_id, subjects)");;
    }

    /**
     * @todo: проанализировать где какие поля используются и вынести в Global Scope
     */
    public function scopeSelectDefault($query)
    {
        return $query->select([
            'tutors.id',
            'first_name',
            'middle_name',
            'subjects',
            'public_desc',
            'photo_extension',
            'start_career_year',
            'birth_year',
            'svg_map',
            'lesson_duration',
            'public_price',
            'departure_price',
            'comment_extended',
            'lk',
            'tb',
            'js',
            \DB::raw('(SELECT COUNT(*) FROM attachments WHERE attachments.tutor_id = tutors.id) as clients_count'),
            \DB::raw('(SELECT MIN(date) FROM attachments WHERE attachments.tutor_id = tutors.id) as first_attachment_date')
        ]);
    }

    /**
     * Iteraction – create row if not exists
     */
    public static function iteraction($id)
    {
        if (! egerep('tutor_iteractions')->where('tutor_id', $id)->exists()) {
            egerep('tutor_iteractions')->insert([
                'tutor_id' => $id
            ]);
        }
        return egerep('tutor_iteractions')->where('tutor_id', $id);
    }

    /**
     * Count tutors
     * @string $type – published | ....
     */
    public static function count($type = null)
    {
        $query = static::query();

        if ($type) {
            switch($type) {
                case 'published':
                    $query->count();
                    break;
                // default count is by subjects
                default:
                    $subject_id = Service\Factory::getSubjectId($type);
                    $query->whereSubject($subject_id);
            }
        } else {
            // если не указан тип, считаем всех преподов
            $query->withoutGlobalScope(TutorScope::class);
        }

        return $query->count();
    }

    /**
     * Get tutors by subject name
     * @string $subject_eng math | eng | ...
     */
    public static function bySubject($subject_eng, $limit = 4)
    {
        $subject_id = Service\Factory::getSubjectId($subject_eng);
        return static::search([
            'subjects' => [$subject_id => true]
        ])->take($limit)->get();
    }

    /**
     * Search tutors by params
     */
    public static function search($search)
    {
        @extract($search);

        // очищаем deselect-значения  {7: false}
        $subjects = array_filter($subjects);

        $query = Tutor::with(['markers']);

        foreach($subjects as $subject_id => $enabled) {
            if ($enabled) {
                $query->whereSubject($subject_id);
            }
        }

        # Оставляем только зеленые маркеры, если клиент едет к репетитору
        if (isset($place) && $place) {
            if ($place == 1) {
                # отсеиваем репетиторов без зеленых маркеров
                $query->has('markers');
            } else {
                # если "строго у себя дома", то только репетиторы с картой выезда
                $query->where('svg_map', '<>', '');
            }
        }

        if (isset($sort)) {
            switch ($sort) {
                case 2:
                    $query->orderBy('public_price', 'desc');
                    break;
                case 3:
                    $query->orderBy('public_price', 'asc');
                    break;
                case 4:
                    $query->leftJoin(DB::raw('(
                        SELECT tt.id, count(DISTINCT r.id) as cnt, sum(r.score) as sm FROM tutors tt
                        join attachments a on a.tutor_id = tt.id
                        join reviews r on r.attachment_id = a.id
                        where r.score between 1 and 10
                        GROUP BY tt.id
                    ) revs'), 'revs.id', '=', 'tutors.id');
                    $query->orderBy(DB::raw('((4 * ((lk + tb + IF(js=10, 8, IF(js=8, 10, js))) / 3) + if(revs.sm is null, 0, revs.sm))/(4 + if(revs.cnt is null, 0, revs.cnt)))'), 'desc');
                case 5:
                    if ($station_id) {
                        $query->has('markers')->orderBy(DB::raw(
                        "(select min(d.distance + m.minutes)
                            from markers mr
                            join metros m on m.marker_id = mr.id
                            join distances d on d.from = m.station_id and d.to = {$station_id}
                            where mr.markerable_id = tutors.id and mr.markerable_type = 'App\\\\Models\\\\Tutor' and mr.type='green'
                        )"), 'asc');
                        break;
                    }
                // @notice break внутри if конструкции чтоб если не указан station_id перескакивал на сортиворку по популярности,
                // что и есть дефолт сортивока изначально насколько я понял.
            }
        }

        $query->selectDefault()->orderBy('clients_count', 'desc');

        return $query;
    }

    /**
     * Get similar tutors
     */
    public static function getSimilar(Tutor $tutor)
    {
        // пока только по предметам похожих находим
        $query = static::select('tutors.id', 'first_name', 'middle_name', 'subjects', 'photo_extension', 'js', 'tb', 'lk')
            ->where('subjects', $tutor->getClean('subjects'))
            ->where('tutors.id', '!=', $tutor->id)
            ->take(3);

        if (count($tutor->markers)) {
            $query->join('markers', function($join) {
                $join->on('markers.markerable_id', '=', 'tutors.id')
                    ->where('markers.markerable_type', '=', 'App\Models\Tutor')
                    ->where('markers.type', '=', 'green');
            })
            ->orderBy(DB::raw("SQRT(
                POW(69.1 * (markers.lat - {$tutor->markers[0]->lat}), 2) +
                POW(69.1 * (37.5382 - {$tutor->markers[0]->lng}) * COS(markers.lat / 57.3), 2)
            )"), 'asc')
            ->groupBy('tutors.id');
        } else {
            $query->inRandomOrder();
        }

        return $query->get();
    }

    public static function boot()
    {
        static::addGlobalScope(new TutorScope);
    }
}

// (
//     SELECT
//         ((4* ((lk + tb + IF(js=10, 8, IF(js=8, 10, js))) / 3) + SUM(r.score))/(4 + COUNT(r.id)))
//     FROM reviews r
//     join attachments a on a.id = r.attachment_id
//     where r.score between 1 and 10
// )
