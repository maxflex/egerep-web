<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\Page;
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
     * Получить отзывы
     */
    public function reviews($id)
    {
        return Tutor::reviews($id)
        ->select(
            'reviews.created_at',
            'reviews.score',
            'reviews.comment',
            'reviews.signature',
            DB::raw('attachments.date as attachment_date'),
            DB::raw('archives.date as archive_date'),
            DB::raw('(SELECT COUNT(*) FROM account_datas ad WHERE ad.tutor_id = attachments.tutor_id AND ad.client_id = attachments.client_id) as lesson_count')
        )->orderBy('reviews.created_at', 'desc')->get();
    }


    /**
     * Поиск по преподам
     */
    public function search(Request $request)
    {
        // потому что надо поменять subjects, а из $request нельзя
        $search = $request->search;

        // // очищаем deselect-значения  {7: false}
        // if (isAssoc($search['subjects'])) {
        //     $search['subjects'] = array_keys(array_filter($search['subjects']));
        // }
        //
        // @extract($search);
        //
        // // пытаемся найти serp-страницу с такими параметрами
        // // если находит при пагинации страницу с похожими параметрами – не редиректить
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
        //
        // // force current page
        // Paginator::currentPageResolver(function() use ($request) {
        //     return $request->page;
        // });

        $tutors = Tutor::search($search)->paginate(10);

        return $tutors;
    }

    public function login()
    {
        if (isset($_SESSION['tutor_id'])) {
            return Tutor::with(['accounts', 'plannedAccount'])->find($_SESSION['tutor_id']);
        }
        abort(401);
    }
}
