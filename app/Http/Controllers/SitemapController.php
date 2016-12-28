<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Sitemap;
use App\Models\Page;
use App\Models\Tutor;

class SitemapController extends Controller
{
    public function index()
    {
        $pages = Page::all();
        Sitemap::addTag(self::_url(), now(), 'daily', '1');
        Sitemap::addTag(self::_url('request'), now(), 'daily', '0.8');
        Sitemap::addTag(self::_url('cv'), now(), 'daily', '0.8');
        Sitemap::addTag(self::_url('login'), now(), 'daily', '0.7');

        foreach ($pages as $page) {
            Sitemap::addTag(self::_url($page->url), $page->updated_at, 'daily', '0.8');
        }
        foreach (Tutor::pluck('id') as $tutor_id) {
            Sitemap::addTag(self::_url($tutor_id), $page->updated_at, 'daily', '0.9');
        }
        return Sitemap::render();
    }

    private static function _url($url = '')
    {
        return config('app.url') . $url;
    }
}
