<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCode extends Model
{
    protected $connection = 'egerep';
    protected $fillable = ['tutor_id'];

    function tutor() {
        return $this->belongsTo(Tutor::class);
    }

    public static function activate($tutor_id, $code)
    {
        $latest_code = self::latest()->where('tutor_id', $tutor_id)->where('activated', false)->first();
        if ($latest_code !== null && $code == $latest_code->code) {
            $latest_code->activated = true;
            $latest_code->save();
            return true;
        }
        return false;
    }

    public static function boot()
    {
        static::creating(function($model) {
            $model->code = mt_rand(1000, 9999);
        });
    }
}
