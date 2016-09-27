<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Tutor;
use DB;

class TutorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Tutor::paginate(10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Засчитать просмотр
     */
    public function view($id)
    {
        DB::connection('egerep')->table('tutors')->whereId($id)->increment('views');
    }

    /**
     * Получить отзывы
     */
    public function reviews($id)
    {
        return Tutor::reviews($id)->select(
            'reviews.score',
            'reviews.comment',
            'reviews.signature'
        )->get();
    }


    /**
     * Поиск по преподам
     */
    public function search(Request $reqeust)
    {
        @extract($reqeust->search);

        // очищаем deselect-значения  {7: false}
        $subjects = array_filter($subjects);

        // если указаны какие либо-параметры, пытаемся найти serp-страницу с параметрами
        if (count($subjects) || $place || $station_id) {
            $page = \App\Models\Page::findByParams($reqeust->search);
            if ($page->exists()) {
                return ['url' => $page->value('url')];
            }
        }

        // force current page
        Paginator::currentPageResolver(function() use ($reqeust) {
            return $reqeust->page;
        });

        $query = Tutor::published()->with(['markers']);

        foreach($subjects as $subject_id => $enabled) {
            if ($enabled) {
                $query->whereRaw("FIND_IN_SET($subject_id, subjects)");
            }
        }

        # Оставляем только зеленые маркеры, если клиент едет к репетитору
        if ($place) {
            if ($place == 1) {
                # если "Клиент едет к репетитору", то только репетиторы с картой выезда
                $query->where('svg_map', '<>', '');
            } else {
                # отсеиваем репетиторов без зеленых маркеров
                $query->has('markers');
            }
        }

        $query->select([
                'id',
                'first_name',
                'middle_name',
                'subjects',
                'public_desc',
                'photo_extension',
                'start_career_year',
                'birth_year',
                'svg_map',
                'lesson_duration',
                'public_price',
                'departure_price',
                DB::raw('(SELECT COUNT(*) FROM attachments WHERE attachments.tutor_id = tutors.id) as clients_count'),
            ])->orderBy('clients_count', 'desc');

        return $query->paginate(10);
    }
}
