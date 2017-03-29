<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    public $timestamps = false;

    /**
     * Посчитать общий дебет
     * $params | date_start | date_end | tutor_id
     */
    public static function sum($params = [])
    {
        return \DB::connection('egerep')->select("
            select sum(debt) as `sum` from debts
            where true "
            . (isset($params['debtor']) ? " and debtor=" . $params['debtor'] : '')
            . (isset($params['after_last_meeting']) ? " and after_last_meeting=" . $params['after_last_meeting'] : '')
            . (isset($params['date_start']) ? " and date>='" . $params['date_start'] . "'" : '')
            . (isset($params['date_end'])   ? " and date<='" . $params['date_end'] . "'" : '')
            . (isset($params['tutor_id'])   ? " and tutor_id='" . $params['tutor_id'] . "'" : '')
        )[0]->sum;
    }

    /**
     * Для страницы «сводка по вечным должникам»
     * для каждого годового промежутка выводится общий дебет по функции Debt::sum
     * минус (сумма дебетов или переплат последней встречи)
     */
    public static function debtors($date_start, $date_end)
    {
        $debt_sum = Debt::sum([
            'after_last_meeting' => 1,
            'date_start' => $date_start,
            'date_end' => $date_end,
            'debtor' => 1,
        ]);

        return $debt_sum + \DB::connection('egerep')->select("select sum(CASE WHEN debt_type=1 THEN (-1 * debt) ELSE debt END) as `sum` FROM (
                     SELECT MAX(date_end) as last_account_date, debt, debt_type, tutor_id
                     FROM accounts WHERE debt IS NOT NULL
                     GROUP BY tutor_id
                   ) x
                   join tutors on x.tutor_id = tutors.id
                   where last_account_date >='{$date_start}' and last_account_date<='{$date_end}' and tutors.debtor=1
       ")[0]->sum;
    }
}
