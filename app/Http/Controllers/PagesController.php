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

    /**
     * Auto login from CRM
     */
    public function login($hash)
    {
        $salt = 'AxQWRu2y3PhE1D';
        $date = date('Y-m-d H');
        $tutor_id_hash = substr($hash, -32, 32);
        $hash = substr($hash, 0, 32);
        if (md5($salt . $date) == $hash) {
            $tutor = Tutor::loggable()->whereRaw("MD5(CONCAT(id, '{$salt}', '{$hash}'))='{$tutor_id_hash}'");
            if ($tutor->exists()) {
                $_SESSION['logged_tutor_id'] = $tutor->value('id');
                return redirect('login');
            }
        }
        abort(404);
    }
}
