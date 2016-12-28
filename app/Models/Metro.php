<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \DB;

class Metro extends Model
{
    protected $connection = 'egerep';

    public $timestamps = false;

    protected $with = ['station'];

    const LINE_COLORS = [
		1 => '#EF1E25',	// Красный
		2 => '#029A55', // Зеленый
		3 => '#0252A2', // Синий
		4 => '#019EE0', // Голубой
		5 => '#745C2F', // Коричневый
		6 => '#C07911', // Оранжевый
		7 => '#B61D8E', // Фиолетовый
		8 => '#FFD803',	// Желтый
		9 => '#ACADAF', // Серый
		10 => '#B1D332',// Салатовый
		11 => '#5091BB',// Бледно-синий (Варшавская)
		12 => '#85D4F3',// Светло-голубая (Бульвар Адмирала Ушакова)
        13 => '#E2A6A6',// Розовая (МЦК)
	];

    // если расстояние больше этого значения, то едем транспортом
    const TRANSPORT_DISTANCE = 1500;

    // ------------------------------------------------------------------------

    public function station()
    {
        return $this->belongsTo('App\Models\Station');
    }

    // ------------------------------------------------------------------------

    /**
     * Расстояние между метками в метрах по умолчанию
     */
    public static function getDistance($lat1, $lng1, $lat2, $lng2, $unit = "K") {
        $theta = $lng1 - $lng2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return round($miles * 1.609344 * 1000); // сразу в метры
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    /**
     * $s - расстояние
     *
     * Если расстояние между двумя точками на карте (неважно это расстояние
     * между меткой репетитора и клиента или расстояние между меткой до метро)
     * до 1500м, то время считается из расчета 100м=1минута пешком. Если расстояние
     * больше 1500м, то время считается из расчета 200м=1 минута на транспорте.
     */
	public static function metersToMinutes($s)
	{
		$k = $s < static::TRANSPORT_DISTANCE ? 1 : 2;
		$t = $s / 100 / $k;
		return round($t, 1);
	}

    /**
     * Время между двумя метками
     */
    public static function minutesBetweenMarkers($marker1, $marker2)
    {
        return static::metersToMinutes(
            static::getDistance($marker1->lat, $marker1->lng, $marker2->lat, $marker2->lng)
        );
    }

    /**
     * Получить время между двумя станциями метро
     */
    public static function minutesBetweenMetros($from, $to)
    {
        if ($from == $to) {
            return;
        }
        $p1 = min($from, $to);
        $p2 = max($from, $to);
        $obj = DB::table('distances')
            ->where('from', $p1)
            ->where('to', $p2)
            ->first();
        if ($obj === null) {
            throw new \Exception("Distance not found", $obj);
        } else {
            return $obj->distance;
        }
    }

    /**
     * Получить ближайшие станции к метке по алгоритму
     */
    public static function getClosest($lat, $lng)
    {
        $metros =  static::getClosestSorted($lat, $lng);

  		// первую самую ближайшую станцию включать всегда
  		$closest_metro = $metros[0];
  		$closest_metro['minutes'] = static::metersToMinutes($closest_metro['meters']);
  		$return[] = $closest_metro;

  		// смотрим другие 2 ближайшие станции
  		foreach (range(1, 2) as $n) {
  			// если до первой другой ближайшей станции расстояние больше,
  			// чем 2x (где х – расстояние до первой ближайшей станции), то завершить
  			if ($metros[$n]['meters'] > ($closest_metro['meters'] * 2)) {
  				break;
  			} else {
  				$metros[$n]['minutes'] = static::metersToMinutes($metros[$n]['meters']);
  				$return[] = $metros[$n];
  			}
  		}

  		return $return;
    }

    /**
	 * Получить n ближайших станций метро массивом от самой ближайшей до самой дальней.
	 *
	 */
	private static function getClosestSorted($lat, $lng, $n = 3)
	{
		$metros = DB::table('stations')->get();

        $return = [];
		foreach ($metros as $metro) {
			$distance = static::getDistance($metro->lat, $metro->lng, $lat, $lng);
			$return[] = (array)$metro + [
				'meters' 	=> $distance,
				'station'	=> [
					'id'	=> $metro->id,
					'title' => $metro->title,
					'color'	=> static::LINE_COLORS[$metro->line_id],
				],
			];
		}

		$d = [];
		foreach ($return as $id => $row) {
			$d[$id] = $row['meters'];
		}
		array_multisort($d, SORT_ASC, $return);
		return array_slice($return, 0, $n);
	}
}
