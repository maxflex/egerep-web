<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Page;
use App\Models\Service\Parser;

class PagesController extends Controller
{
    /**
     * Все страницы
     */
    public function index($url = '')
    {
        $page = Page::where('url', $url)->first();
        return view('pages.index')->with(['html' => $page->html]);
    }

    /**
     * Tutor profile page
     */
    public function tutor($id)
    {
        $html = Page::where('url', 'tutor')->first()->html;
        Parser::compileTutor($id, $html);
        return view('pages.index')->with(compact('html'));
    }
}
