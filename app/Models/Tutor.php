<?php

namespace App\Models;

use Shared\Model;
use App\Scopes\TutorScope;
use App\Scopes\ReviewScope;
use DB;
use App\Models\Queries\TutorQuery;

class Tutor extends Model
{
    protected $connection = 'egerep';
    static $phone_fields = ['phone', 'phone2', 'phone3', 'phone4'];
    protected $appends = [
        'photo_url',
        'review_avg',
        'reviews_count',
        'subjects_string',
    ];

    const SERVER_URL = 'https://lk.ege-repetitor.ru/img/tutors/';
    const NO_PHOTO   = 'no-profile-img.gif';

    protected $commaSeparated = ['svg_map', 'subjects', 'grades', 'branches'];

    public function markers()
    {
        return $this->morphMany(Marker::class, 'markerable')->where('type', 'green');
    }

    public function accounts()
    {
        return $this->hasMany(Account::class)->latest()->take(3);
    }

    public function plannedAccount()
    {
        return $this->hasOne(PlannedAccount::class);
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
            case 6:
            case 10: {
                $js = 8;
                break;
            }
            case 8: {
                $js = 10;
                break;
            }
            case 7: {
                $js = 9;
                break;
            }
            default: {
                $js = $this->js;
            }
        }
        $avg = (4 * (($this->lk + $this->tb + $js) / 3) + $sum)/(4 + $count);
        return number_format($avg, 1, ',', '');
    }

    public function getSubjectsStringAttribute()
    {
        return implode(', ', array_map(function($subject_id) {
            return dbFactory('subjects')->whereId($subject_id)->value('dative');
        }, $this->subjects));
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
            'subjects'      => [$subject_id],
            'with_pictures' => true,
        ])->take($limit)->get();
    }

    /**
     * Search tutors by params
     */
    public static function search($search)
    {
        @extract($search);

        // очищаем deselect-значения  {7: false}
        if (isAssoc($subjects)) {
            $subjects = array_keys(array_filter($subjects));
        }

        $query = Tutor::with(['markers']);

        foreach($subjects as $subject_id) {
            $query->whereSubject($subject_id);
        }

        if (isset($with_pictures)) {
            $query->where('photo_extension', '!=', '');
        }

        # Оставляем только зеленые маркеры, если клиент едет к репетитору
        if (isset($place)) {
            if ($place == 1) {
                # отсеиваем репетиторов без зеленых маркеров
                $query->has('markers');
            } else {
                # если "строго у себя дома", то только репетиторы с картой выезда
                $query->where('svg_map', '<>', '');
            }
        }

        if (isset($hidden_filter)) {
            $hidden_filter_conditions = [];
            foreach($hidden_filter as $phrase) {
                $hidden_filter_conditions[] = "(
                    LOWER(public_desc) REGEXP '[[:<:]]{$phrase}[[:>:]]'
                    OR LOWER(education) REGEXP '[[:<:]]{$phrase}[[:>:]]'
                    OR EXISTS (
                        SELECT 1 FROM reviews r
                        JOIN attachments a on a.id = r.attachment_id
                        WHERE LOWER(r.comment) REGEXP '[[:<:]]{$phrase}[[:>:]]' AND a.tutor_id = tutors.id AND r.state = 'published'
                    )
                )";
            }
            $query->whereRaw('(' . implode(' OR ', $hidden_filter_conditions) . ')');
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
                    $query->orderBy(DB::raw('((4 * ((lk + tb +
                        CASE js
                            WHEN 6 THEN 8
                            WHEN 10 THEN 8
                            WHEN 8 THEN 10
                            WHEN 7 THEN 9
                            ELSE js
                        END
                    ) / 3) + if(revs.sm is null, 0, revs.sm))/(4 + if(revs.cnt is null, 0, revs.cnt)))'), 'desc');
                case 5:
                    if ($station_id) {
                        if (! isset($place)) {
                            $query->orderBy(DB::raw("LEAST(" . TutorQuery::orderByMarkerDistance($station_id) . ", " . TutorQuery::orderByMetroDistance($station_id) . ")"), "asc");
                        } else if ($place == 1) {
                            $query->orderBy(DB::raw(TutorQuery::orderByMarkerDistance($station_id)), 'asc');
                        } else if ($place == 2) {
                            $query->orderBy(DB::raw(TutorQuery::orderByMetroDistance($station_id)));
                        }
                    }
                    break;
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
        $query = Tutor::select('tutors.id', 'first_name', 'middle_name', 'subjects', 'photo_extension', 'js', 'tb', 'lk')
            ->where('subjects', $tutor->getClean('subjects'))
            ->where('tutors.id', '!=', $tutor->id)
            ->take(3);

        if (count($tutor->markers)) {
            $query->join('markers', function($join) {
                $join->on('markers.markerable_id', '=', 'tutors.id')
                    ->where('markers.markerable_type', '=', 'App\Models\Tutor');
            })
            ->orderBy(DB::raw("SQRT(
                POW(69.1 * (markers.lat - {$tutor->markers[0]->lat}), 2) +
                POW(69.1 * ({$tutor->markers[0]->lng} - markers.lng) * COS(markers.lat / 57.3), 2)
            )"), 'asc')
            ->groupBy('tutors.id');
        } else {
            $query->inRandomOrder();
        }

        return $query->get();
    }

    /**
     * Найти по номеру телефона
     */
    public function scopeFindByPhone($query, $phone)
    {
        $sql = [];
        $phone = cleanNumber($phone);
        foreach (static::$phone_fields as $phone_field) {
            $sql[] = "{$phone_field} = '$phone'";
        }
        return $query->whereRaw('(' . implode(' OR ', $sql) . ')');
    }

    /**
     * Преподы, которые могут логиниться
     */
    public function scopeLoggable($scope)
    {
        return $scope->where('debt_calc', '>', 0);
    }

    public static function boot()
    {
        static::addGlobalScope(new TutorScope);
    }
}
