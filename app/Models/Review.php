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
        'score',
        'state',
        'updated_at',
        'created_at',
        'errors',
        'user_id',
    ];

    public function tutor()
    {
        return $this->belongsToThrough(Tutor::class, Attachment::class)->select(
            'tutors.id',
            'tutors.first_name',
            'tutors.last_name',
            'tutors.middle_name',
            'tutors.subjects',
            'tutors.photo_extension'
        );
    }

    public static function get($limit = 3)
    {
        return static::with('tutor')->take($limit)->orderBy('created_at', 'desc')->get();
    }

    public static function boot()
    {
        static::addGlobalScope(new ReviewScope);
    }
}
