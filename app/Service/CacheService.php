<?php
/**
 * Сервис для работы с кешем
 */
namespace App\Service;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    # статичная переменная для хранения время жизни кеша
    private static $cacheExparis;

    # указаетель функций которе можно кешировать
    private static $allowFunctions = ['count'];

    # перфикс для ключа кеша
    protected static $perfix = 'egerep-web:functions';

    /**
     * Cache constructor.
     */
    public function __construct()
    {
        # заполняем статичную переменную для хранения время жизни из ENV файла
        self::$cacheExparis = Carbon::now()->addMinutes(env('CACHE_EXPARES', 10));
    }

    /**
     * получение кеша
     * @param string $function_name название функции
     * @param array $args аргументы функции
     * @return mixed
     */
    public function get($function_name = "", $args = [])
    {
        return Cache::get($this->getKey($function_name, $args));
    }

    /**
     * сохранение кеша
     * @param string $function_name название функции
     * @param array $args аргументы функции
     * @param string $replacement = данные для сохранения
     */
    public function put($function_name = "", $args = [], $replacement = '')
    {
        Cache::put($this->getKey($function_name, $args), $replacement, self::$cacheExparis);
    }

    /**
     * генератор ключа по названию и перменным функции
     * @param string $function_name название функции
     * @param array $args аргументы функции
     * @return string
     */
    private function getKey($function_name = "", $args = [])
    {
        return self::$perfix . ':' . $function_name . ':' . implode(':', $args);
    }

    /**
     * проверяем нужно ли кешировать результат функции
     * @param string $function_name название функции
     * @return bool
     */
    public function isAllow($function_name = "")
    {
        return in_array($function_name, self::$allowFunctions);
    }

}