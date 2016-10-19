<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Controllers\Api\RequestsController;

class RequestsControllerTest extends TestCase
{
    use DatabaseTransactions;

    const PHONE_NUMBER = '+7 (920) 555-67-76';

    /**
     * Отправка заявки
     *
     * @test
     */
    public function params_check()
    {
        // Номер телефона
        foreach(['', '+7 (111) 111-11-11', '9205556776', '89252727210'] as $phone) {
            $this->post(route('api.requests.store'), self::params())->seeStatusCode(302);
        }
        $this->post(route('api.requests.store'), self::params(self::PHONE_NUMBER))->seeStatusCode(200);

        // Имя
        $this->post(route('api.requests.store'), self::params(self::PHONE_NUMBER, self::str(MAX_NAME_LENGTH + 1)))->seeStatusCode(302);
        $this->post(route('api.requests.store'), self::params(self::PHONE_NUMBER, self::str(MAX_NAME_LENGTH)))->seeStatusCode(200);
        $this->post(route('api.requests.store'), self::params(self::PHONE_NUMBER, '%'))->seeStatusCode(302);

        // Описание
        $this->post(route('api.requests.store'), self::params(self::PHONE_NUMBER, '', self::str(MAX_COMMENT_LENGTH + 1)))->seeStatusCode(302);
        $this->post(route('api.requests.store'), self::params(self::PHONE_NUMBER, '', self::str(MAX_COMMENT_LENGTH)))->seeStatusCode(200);
        $this->post(route('api.requests.store'), self::params(self::PHONE_NUMBER, '', '%'))->seeStatusCode(302);
    }

    private static function params($phone = '', $name = '', $comment = '')
    {
        return compact('name', 'phone', 'comment');
    }

    private static function str($length)
    {
        return str_repeat('А', $length);
    }
}
