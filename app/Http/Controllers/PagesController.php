<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Page;

class PagesController extends Controller
{
    /**
     * Все страницы
     */
    public function index($url = '')
    {
        $page = Page::where('url', $url)->first();
        return view('pages.index')->with(compact('page'));
    }
}
