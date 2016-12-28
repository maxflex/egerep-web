<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Page;
use App\Models\Variable;
use App\Models\Tutor;
use App\Models\Service\Parser;

class PagesController extends Controller
{
    /**
     * Все страницы (на самом деле это теперь только серп)
     */
    public function index($url = '')
    {
        $page = Page::whereUrl($url);
        if (! $page->exists()) {
            $html = Variable::display('page-404');
        } else {
            $html = $page->first()->html;
        }
        $_SESSION['action'] = 'serp';
        return view('pages.index')->with(compact('html'));
    }

    /**
     * Tutor profile page
     */
    public function tutor($id)
    {
        if (Tutor::whereId($id)->exists()) {
            $html = Variable::display('page-tutor-profile');
            Parser::compileTutor($id, $html);
        } else {
            $html = Variable::display('page-404');
        }
        $_SESSION['action'] = 'profile';
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
                Tutor::login($tutor->value('id'), false);
                return redirect('login');
            }
        }
        abort(404);
    }
}
