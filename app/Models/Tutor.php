<?php

namespace App\Models;

use Shared\Model;
use App\Scopes\TutorScope;
use App\Scopes\ReviewScope;
use DB;
use App\Models\Queries\TutorQuery;
use App\Service\Cacher;

class Tutor extends Model
{
    protected $connection = 'egerep';
    static $phone_fields = ['phone', 'phone2', 'phone3', 'phone4'];
    protected $appends = [
        'photo_url',
        'subjects_string',
        'subjects_string_common',
    ];

    const SERVER_URL = 'https://lk.ege-repetitor.ru/img/tutors/';
    const NO_PHOTO   = 'no-profile-img.gif';
    const USER_TYPE  = 'TEACHER';

    protected $commaSeparated = ['subjects', 'grades', 'branches'];

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

    public function getPhotoUrlAttribute()
    {
        $filename = self::SERVER_URL . $this->id . '.' . $this->photo_extension;
        if ($this->photo_extension && fileExists($filename)) {
            return $filename;
        } else {
            return self::SERVER_URL . self::NO_PHOTO;
        }
    }

    public function getSubjectsStringAttribute()
    {
        return implode(', ', array_map(function($subject_id) {
            return Cacher::getSubjectName($subject_id, 'dative');
        }, $this->subjects));
    }

    public function getSubjectsStringCommonAttribute()
    {
        return implode(', ', array_map(function($subject_id) {
            return Cacher::getSubjectName($subject_id, 'name');
        }, $this->subjects));
    }

    public static function reviews($tutor_id)
    {
        return DB::connection('egerep')
            ->table('reviews')
            ->join('attachments', 'attachments.id', '=', 'attachment_id')
            ->join('archives', 'archives.attachment_id', '=', 'attachments.id')
            ->where('tutor_id', $tutor_id)
            ->where('reviews.state', 'published')
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
            'lesson_duration',
            'public_price',
            'departure_price',
            'comment_extended',
            'education',
            'achievements',
            'preferences',
            'experience',
            'gender',
            'lk',
            'tb',
            'js',
            'tutor_data.clients_count',
            'tutor_data.reviews_count',
            'tutor_data.first_attachment_date',
            'tutor_data.review_avg',
            'tutor_data.svg_map',
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

        // \DB::statement('set session query_cache_type=0');

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
                $query->has('departure');
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
                    $query->orderBy('review_avg', 'desc');
                    break;
                case 5:
                    if ($station_id) {
                        if ($place == 1) {
                            $query->orderByDistanceToMarkers($station_id, 'green');
                        } else if ($place == 2) {
                            $query->orderByIntersectingMetro($station_id)->orderByDistanceToMarkers($station_id);
                        }
                    }
                    break;
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
        $query = Tutor::selectDefault()->addSelect(DB::raw('TRUE as is_similar'))
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

        return $query->with(['markers'])->get();
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
        return $scope->where('debt_calc', '>', 0);
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

    public static function boot()
    {
        static::addGlobalScope(new TutorScope);
    }
}
