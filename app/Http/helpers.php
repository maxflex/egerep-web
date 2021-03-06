<?php

    const NAME_VALIDATION_REGEX = '/^[A-Za-zа-яА-ЯёЁ\-\s]*$/u';
    const TEXT_VALIDATION_REGEX = '/^[0-9A-Za-zа-яА-ЯёЁ\(\)\-\.\,\s]*$/u';
    const PHONE_REGEX = '/^[4|9]{1}/';
    const MOBILE_PHONE_REGEX = '/^\+7 \(9[\d]{2}\) [\d]{3}-[\d]{2}-[\d]{2}$/';
    const MAX_COMMENT_LENGTH = 1000;
    const MAX_NAME_LENGTH    = 60;

    function dateRange($strDateFrom, $strDateTo)
    {
        $aryRange=array();

        $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
        $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

        if ($iDateTo>=$iDateFrom)
        {
            array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
            while ($iDateFrom<$iDateTo)
            {
                $iDateFrom+=86400; // add 24 hours
                array_push($aryRange,date('Y-m-d',$iDateFrom));
            }
        }
        return $aryRange;
    }

    function preType($Object)
    {
        echo '<pre>';
        print_r($Object);
        echo '</pre>';
    }

    function emptyObject()
    {
        return (object)[];
    }

    function setOrNull($var)
    {
        return (isset($var) && $var) ? $var : null;
    }

    /**
	 * Форматировать дату в наш формат.
	 *
	 */
	function dateFormat($date, $notime = false)
	{
		$date = date_create($date);
		return date_format($date, $notime ? "d.m.Y" : "d.m.Y в H:i");
	}

    /**
     * Деформатировать дату
     */
    function fromDotDate($date)
    {
        $parts = explode('.', $date);
        return implode('-', array_reverse($parts));
    }

    /**
     * Возвратить чистый номер телефона.
     *
     */
    function cleanNumber($number, $add_seven = false)
    {
        $number = preg_replace("/[^0-9]/", "", $number);
        if ($number && $number[0] != '7') {
            $number = '7' . $number;
        }
        return $number;
    }

    /**
     * Пребежать по номерам телефона
     * функция должна возвращать номер телефона
     * @example throughNumbers($tutor, function($number)) {
     *              return $number . '123';
     *          }
     *
     */
    function throughNumbers(&$object, $func)
    {
        foreach (App\Traits\Person::$phone_fields as $phone_field) {
            $object->{$phone_field} = $func($object->{$phone_field});
        }
    }

    /**
     * Очистить номера телефонов у объекта
     */
    function cleanNumbers(&$object)
    {
        throughNumbers($object, 'cleanNumber');
    }

    /*
	 * В формат ангуляра
	 */
	function ngInitOne($name, $Object)
	{
		return $name." = ".htmlspecialchars(json_encode($Object, JSON_NUMERIC_CHECK)) ."; ";
	}

	/*
	 * Инициализация переменных ангуляра
	 * $array – [var_name = {var_values}; ...]
	 * @return строка вида 'a = {test: true}; b = {var : 12};'
	 */
	function ngInit($array)
	{
        $return = '';
		foreach ($array as $var_name => $var_value) {
			// Если значение не установлено, то это пустой массив по умолчанию
			// if (!$var_value && !is_int($var_value)) {
			// 	$var_value = "[]";
			// } else {
				// иначе кодируем объект в JSON
				// $var_value = htmlspecialchars(json_encode($var_value, JSON_NUMERIC_CHECK));
				$var_value = htmlspecialchars(json_encode($var_value, JSON_FORCE_OBJECT));
			// }
			$return .= $var_name." = ". $var_value ."; ";
		}

		return ['nginit' => $return];
	}

    function isProduction()
    {
        return app()->environment() == 'production';
    }

    /**
     * Возвратить user id из сесси или 0 (system)
     */
    function userIdOrSystem()
    {
        return \App\Models\User::loggedIn() ? \App\Models\User::fromSession()->id : 0;
    }

    function now($no_time = false)
    {
        return date('Y-m-d' . ($no_time ? '' : ' H:i:s'));
    }

    function isBlank($value) {
        return empty($value) && !is_numeric($value);
    }

    function notBlank($value) {
        return ! isBlank($value);
    }

    function isFilled($value)
    {
        return (isset($value) && ! empty($value));
    }

    /**
     * Разбить enter'ом
     */
    function breakLines($array)
    {
        return implode('

', array_filter($array));
    }


    /**
     * Удалить пустые строки
     */
    function filterParams($a)
    {
        return (object)array_filter((array)$a, function($e) {
            return $e !== '';
        });
    }

    function pluralize($one, $few, $many, $n)
	{
		$text = $n%10==1&&$n%100!=11?$one:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$few:$many);
        return $n . ' ' . $text;
	}

    /**
     * Get from factory
     */
    function fact($table, $key = 'id', $select = null, $orderBy = null)
    {
        // if ($table == 'priorities') {
        //     return dd($orderBy);
        // }
        return \App\Models\Service\Factory::json($table, $key, $select, $orderBy);
    }

    /**
     * Egerep connection helper
     */
    function egerep($table) {
        return \DB::connection('egerep')->table($table);
    }

    /**
     * Factory connection helper
     * @return [type]  [description]
     */
    function dbFactory($table)
    {
        return \DB::connection('factory')->table($table);
    }


    function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    function getSize($file, $precision = 1)
    {
        $size = filesize($file);
        $base = log($size, 1024);
        $suffixes = array('байт', 'Кб', 'Мб', 'Гб', 'Тб');
        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }

    function isMobile($raw = false)
    {
        $agent = new \Jenssegers\Agent\Agent;
        $is_mobile = ($agent->isMobile() && ! $agent->isTablet());
        if ($raw) {
            return $is_mobile;
        } else {
            return (($is_mobile && ! isset($_SESSION['force_full'])) || (isset($_SESSION['force_mobile'])));
        }
    }

    function fileExists($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if( $httpCode == 200 ){return true;}
    }

    function isIndexPage()
    {
        return $_SERVER['REQUEST_URI'] == '/';
    }

    function isSerpPage()
    {
        return \DB::table('pages')->whereUrl(substr($_SERVER['REQUEST_URI'], 1))->exists();
    }


    function isTestSubdomain()
    {
        if (\App::environment('local')) {
            return true;
        }
        $parts = explode('.', @$_SERVER['HTTP_HOST']);
        return array_shift($parts) === 'test';
    }

    function isDevSubdomain()
    {
        if (\App::environment('local')) {
            return false;
            return true;
        }
        $parts = explode('.', @$_SERVER['HTTP_HOST']);
        return array_shift($parts) === 'dev';
    }

    function yearsPassed($year)
    {
        return date('Y') - $year;
    }

    function isExperiment($key = AB_TEST_KEY)
    {
      return isset($_COOKIE[$key]) && $_COOKIE[$key];
    }
