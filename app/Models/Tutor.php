<?php

namespace App\Models;

use Shared\Model;
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
        return number_format(DB::connection('egerep')->table('reviews')->join('attachments', 'attachments.id', '=', 'attachment_id')->where('tutor_id', $this->id)->whereBetween('score', [1, 10])->select('reviews.score')->avg('reviews.score'), 1, ',', '');
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

    public function scopePublished($query)
    {
        return $query->where('public_desc', '!=', '');
    }
}
