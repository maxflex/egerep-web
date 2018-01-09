<?php

namespace App\Models;

use App\Scopes\TutorScope;
use App\Scopes\ReviewScope;
use DB;
use App\Models\Queries\TutorQuery;
use App\Service\Cacher;
use App\Service\Months;

class Tutor extends Service\Model
{
    protected $connection = 'egerep';
    static $phone_fields = ['phone', 'phone2', 'phone3', 'phone4'];
    protected $appends = [
        'subjects_string',
        'subjects_string_common',
        'subjects_with_types',
        'displayed_reviews',
        'types',
    ];

    const USER_TYPE  = 'TEACHER';

    protected $commaSeparated = ['subjects', 'grades', 'branches'];

    protected $multiLine = ['public_desc', 'education', 'achievements', 'experience', 'preferences'];

    public function departure()
    {
        return $this->hasMany(TutorDeparture::class);
    }

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

    public function getDebtCalcAttribute()
    {
        return round(Debt::sum([
            'tutor_id' => $this->id,
            'after_last_meeting' => 1
        ]));
    }

    public function getSubjectsStringAttribute()
    {
        return implode(', ', array_map(function($subject_id) {
            return Cacher::getSubjectName($subject_id, 'dative');
        }, $this->subjects));
    }

    public function getDisplayedReviewsAttribute()
    {
        return self::getReviews($this->id, 2);
    }

    public function getSubjectsStringCommonAttribute()
    {
        return implode(', ', array_map(function($subject_id) {
            return Cacher::getSubjectName($subject_id, 'name');
        }, $this->subjects));
    }

    public function getSubjectsWithTypesAttribute()
    {
        $types = $this->types;

        $subjects = array_map(function($subject_id) {
            return Cacher::getSubjectName($subject_id, 'name');
        }, $this->subjects);

        if (count($types)) {
            foreach($subjects as $index => $subject) {
                $subjects[$index] = $subject . ' (+' . implode(', ', $types) . ')';
            }
        }

        return $subjects;
    }

    /**
     * Типы подготовки (ЕГЭ, ОГЭ или ничего)
     */
    public function getTypesAttribute()
    {
        $types = [];
        if ($this->grades !== null) {
            if (in_array(11, $this->grades)) {
                $types[] = 'ЕГЭ';
            }
            if (in_array(9, $this->grades)) {
                $types[] = 'ОГЭ';
            }
        }
        return $types;
    }

    public static function reviews($tutor_id)
    {
        return DB::connection('egerep')
            ->table('reviews')
            ->join('attachments', 'attachments.id', '=', 'attachment_id')
            ->where('tutor_id', $tutor_id)
            ->where('reviews.state', 'published')
            ->whereBetween('score', [1, 10]);
    }

    public function scopeWhereSubject($query, $subject_id)
    {
        return $query->whereRaw("FIND_IN_SET($subject_id, subjects)");
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
            'lesson_duration',
            'public_price',
            'departure_price',
            'education',
            'achievements',
            'preferences',
            'experience',
            'tutoring_experience',
            'grades',
            'gender',
            'lk',
            'tb',
            'js',
            'created_at',
            'tutor_data.clients_count',
            'tutor_data.reviews_count',
            'tutor_data.review_avg',
            'tutor_data.svg_map',
            'tutor_data.photo_exists',
        ])->join('tutor_data', 'tutor_data.tutor_id', '=', 'tutors.id');
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

        $query = Tutor::with(['markers'])->selectDefault();

        foreach($subjects as $subject_id) {
            $query->whereSubject($subject_id);
        }

        if (isset($with_pictures)) {
            $query->where('photo_extension', '!=', '');
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

        if (isset($tutor_id) && $tutor_id) {
            $query->orderBy(DB::raw("FIELD(id,{$tutor_id})"), 'desc');
        }

        // if (! @isBlank($age_from) || ! @isBlank($age_to)) {
        //     $age_from = @isBlank($age_from) ? 0 : filter_var($age_from, FILTER_SANITIZE_NUMBER_INT);
        //     $age_to = @isBlank($age_to) ? 999 : filter_var($age_to, FILTER_SANITIZE_NUMBER_INT);
        //     if ($age_to >= $age_from) {
        //         $query->addSelect(DB::raw("IF((YEAR(NOW()) - birth_year) between {$age_from} and {$age_to}, 1, 0) as age_order"))->orderBy('age_order', 'desc');
        //     }
        // }

        // if (! @isBlank($gender)) {
        //     $query->addSelect(DB::raw("IF(gender='{$gender}', 1, 0) as gender_order"))->orderBy('gender_order', 'desc');
        // }

        if (isset($priority)) {
            switch ($priority) {
                // у репетитора
                case 2:
                    # отсеиваем репетиторов без зеленых маркеров
                    $query->has('markers')->orderByDistanceToMarkers($station_id, 'green');
                    break;
                // у себя дома
                case 3:
                    $query->has('departure')->orderByIntersectingMetro($station_id)->orderByDistanceToMarkers($station_id);
                    break;
                // сначала дороже
                case 4:
                    $query->orderBy('public_price', 'desc');
                    break;
                // сначала дешевле
                case 5:
                    $query->orderBy('public_price', 'asc');
                    break;
                // по средней оценке
                case 6:
                    $query->orderBy('review_avg', 'desc');
                    break;
                // (1) по популярности
                default:
                    $query->orderBy('clients_count', 'desc');
            }
        } else {
            $query->orderBy('clients_count', 'desc');
        }
        return $query;
    }

    /**
     * Get similar tutors
     */
    public static function getSimilar(Tutor $tutor)
    {
        // пока только по предметам похожих находим
        $query = Tutor::with(['markers'])->selectDefault()->addSelect(DB::raw('TRUE as is_similar'))
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
     * $log_time – логировать время входа?
     */
    public static function login($tutor_id, $log_time = true)
    {
        $_SESSION['tutor_id'] = $tutor_id;
        if ($log_time) {
            egerep('tutors')->whereId($tutor_id)->update([
                'last_login_time' => now()
            ]);
        }
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
        return $scope->whereRaw("(select sum(debt) from debts where after_last_meeting = 1 and tutor_id = tutors.id) > 0");
    }

    /**
     * Сначала идут преподы, которые выезжают на выбранную станцию метро
     */
    public static function scopeOrderByIntersectingMetro($query, $station_id)
    {
        return $query->orderBy(DB::raw("exists (select 1 from tutor_departures where station_id={$station_id} and tutor_id = tutors.id)"), 'desc');
    }

    /**
     * От метки препода до станции метро
     */
    public static function scopeOrderByDistanceToMarkers($query, $station_id, $marker_type = null)
    {
        return $query->orderBy(DB::raw("
            IFNULL(
                (
                    select min(minutes) from tutor_distances
                    where station_id = {$station_id} and tutor_id = tutors.id " . ($marker_type ? " and marker_type='{$marker_type}'" : '') . "
                ),
                999999
            )
        "));
    }

    public static function getReviews($id, $take = null)
    {
        // attachment-refactored
        $reviews = Tutor::reviews($id)
            ->select(
                'reviews.created_at',
                'reviews.score',
                'reviews.ball',
                'reviews.max_ball',
                'reviews.comment',
                'reviews.signature',
                DB::raw('(reviews.ball / reviews.max_ball) as ball_efficency')
            )
            ->orderBy(DB::raw("
                CASE
                    WHEN ball_efficency >= 0.81 THEN 6
                    WHEN ball_efficency >= 0.71 THEN 5
                    WHEN ball_efficency >= 0.61 THEN 4
                    WHEN ball_efficency >= 0.51 THEN 3
                    WHEN ball_efficency >= 0.41 THEN 2
                    ELSE 1
                END
            "), 'desc')
            ->orderBy('reviews.created_at', 'desc');

        if ($take) {
            $reviews->take($take);
        }

        $reviews = $reviews->get();

        foreach($reviews as &$review) {
            $review->date_string = date('j ', strtotime($review->created_at)) . Months::SHORT[date('n', strtotime($review->created_at))] . date(' Y', strtotime($review->created_at));
        }

        return $reviews;
    }

    public static function boot()
    {
        static::addGlobalScope(new TutorScope);
    }
}