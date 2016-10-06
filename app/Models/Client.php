<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Client extends Model
{
    protected $connection = 'egerep';

    public static function count()
    {
        return DB::connection('egerep')->table('attachments')->count('client_id');
    }
}
