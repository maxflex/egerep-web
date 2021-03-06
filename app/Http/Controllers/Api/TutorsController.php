<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\Page;
use App\Models\Marker;
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
        return Tutor::with('markers')->selectDefault()->find($id);
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
     * Получить отзывы
     */
    public function reviews($id)
    {
        return Tutor::getReviews($id);
    }


    /**
     * Поиск по преподам
     */
    public function search(Request $request)
    {
        // потому что надо поменять subjects, а из $request нельзя
        $search = $request->search;

        $search['tutor_id'] = $request->tutor_id;

        // очищаем deselect-значения  {7: false}
        if (isAssoc($search['subjects'])) {
            $search['subjects'] = array_keys(array_filter($search['subjects']));
        }

        @extract($search);

        // пытаемся найти serp-страницу с такими параметрами
        // если находит при пагинации страницу с похожими параметрами – не редиректить
        // if ($request->filter_used && $request->page < 2) {
        //     $page = Page::findByParams($search);
        //     if ($page->exists()) {
        //         return ['url' => $page->inRandomOrder()->value('url')];
        //     } else
        //     if ($id != 10) {
        //         unset($_COOKIE['search']);
        //         return ['url' => Page::whereId(10)->value('url')];
        //     }
        // }

        // force current page
        Paginator::currentPageResolver(function() use ($request) {
            return $request->page;
        });

        $tutors = Tutor::search($search)->paginate(10);

        return $tutors;
    }

    public function markers(Request $request)
    {
        $subjects = [];

        if (isAssoc($request->subjects)) {
            $subjects = array_keys(array_filter($request->subjects));
        }

        $query = egerep('markers as m')->join('tutors as t', function($join) {
            return $join->on('t.id', '=', 'm.markerable_id')->where('m.markerable_type', '=', 'App\Models\Tutor');
        })->select('m.markerable_id', 'm.lat', 'm.lng', DB::raw("'green' as `type`"))->where('t.public_desc', '<>', '');

        if ($request->map_priority == 3 && $request->map_station_id) {
            // $query->addSelect(DB::raw("EXISTS (SELECT 1 FROM tutor_departures td WHERE td.tutor_id=t.id AND td.station_id={$request->map_station_id}) as can_departure"));
            $query->whereRaw("EXISTS (SELECT 1 FROM tutor_departures td WHERE td.tutor_id=t.id AND td.station_id={$request->map_station_id})");
        } else {
            $query->where('m.type', 'green');
        }

        foreach($subjects as $subject_id) {
            $query->whereRaw("FIND_IN_SET($subject_id, t.subjects)");
        }

        return $query->get();
    }
}
