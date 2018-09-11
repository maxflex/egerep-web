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
        $_SESSION['action'] = 'serp';
        $page = Page::whereUrl($url);
        if (! $page->exists()) {
            $html = Variable::display('page-404');
            $status = 404;
        } else {
            $html = $page->first()->html;
            $status = 200;
        }
        return response()->view('pages.index', compact('html'), $status);
    }

    /**
     * Tutor profile page
     */
    public function tutor($id)
    {
        if (Tutor::whereId($id)->exists()) {
            $variable = 'page-tutor-profile';
            if (isDevSubdomain()) {
                $variable .= '-dev';
            }
            $html = Variable::display($variable);
            Parser::compileTutor($id, $html);
            $status = 200;
        } else {
            $html = Variable::display('page-404');
            $status = 404;
        }
        $_SESSION['action'] = 'profile';
        return response()->view('pages.index', compact('html'), $status);
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
