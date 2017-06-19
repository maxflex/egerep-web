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
        $query = static::has('tutor')->with('tutor')
            ->take($limit)->join('attachments', 'attachments.id', '=', 'attachment_id')
            ->select('reviews.*')
            ->addSelect(DB::raw('(SELECT COUNT(*) FROM account_datas ad WHERE ad.tutor_id = attachments.tutor_id AND ad.client_id = attachments.client_id) as lesson_count'));
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
