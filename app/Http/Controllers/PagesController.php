<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Page;
use App\Models\Tutor;
use App\Models\Service\Parser;

class PagesController extends Controller
{
    /**
     * Все страницы
     */
    public function index($url = '')
    {
        $page = Page::whereUrl($url);

        if (! $page->exists()) {
            $page = Page::whereUrl(404);
        }

        return view('pages.index')->with(['html' => $page->first()->html]);
    }

    /**
     * Tutor profile page
     */
    public function tutor($id)
    {
        if (Tutor::whereId($id)->exists()) {
            $html = Page::whereUrl('tutor')->first()->html;
            Parser::compileTutor($id, $html);
        } else {
            $html = Page::whereUrl(404)->first()->html;
        }
        return view('pages.index')->with(compact('html'));
    }
}
