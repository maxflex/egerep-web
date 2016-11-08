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

    public static function get($tutor_id, $code)
    {
        return self::latest()->where('tutor_id', $tutor_id)->where('code', $code)->where('activated', false)->first();
    }

    public function activate()
    {
        $this->activated = true;
        $this->save();
    }

    public static function boot()
    {
        static::creating(function($model) {
            $model->code = mt_rand(1000, 9999);
        });
    }
}
