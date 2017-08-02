<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ReviewScope;
use \Znck\Eloquent\Traits\BelongsToThrough;
use DB;

class Review extends Model
{
    use BelongsToThrough;

    protected $connection = 'egerep';

    protected $hidden = [
        'attachment_id',
        'state',
        'updated_at',
        'errors',
        'user_id',
    ];

    protected $appends = ['date_string'];

    public function getDateStringAttribute()
    {
        $date = $this->attributes['created_at'];
        return date('j ', strtotime($date)) . Months::SHORT[date('n', strtotime($date))] . date(' Y', strtotime($date));
    }

    public function tutor()
    {
        return $this->belongsToThrough(Tutor::class, Attachment::class)->select(
            'tutors.id',
            'tutors.first_name',
            'tutors.last_name',
            'tutors.middle_name',
            'tutors.subjects',
            'tutors.photo_extension',
            'tutor_data.photo_exists'
        )->join('tutor_data', 'tutor_data.tutor_id', '=', 'tutors.id');
    }

    public static function get($limit = 3, $random = false)
    {
        // @todo globalScope для tutor даже при eager loading не применяется для tutor а.
        // attachment-refactored
        $query = static::has('tutor')->with('tutor')
            ->take($limit)
            ->select('reviews.*');
        if ($random) {
            return $query->inRandomOrder()->first();
        } else {
            return $query->orderBy('reviews.created_at', 'desc')->get();
        }
    }

    public static function boot()
    {
        static::addGlobalScope(new ReviewScope);
    }
}
